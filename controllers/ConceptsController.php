<?php
require_once 'controllers/Controller.php';
require_once 'models/PaymentConcept.php';
require_once 'models/SchoolBankAccount.php';

/**
 * Conceptos de cobro (inscripcion, mensualidad, uniformes...) y sus tarifas. Solo el administrador.
 *
 * Cambiar una tarifa NO modifica pagos ya generados: cada pago conserva su propio monto. Un concepto
 * con pagos asociados no se borra: se desactiva (deja de ofrecerse en cobros nuevos).
 */
class ConceptsController extends Controller {

    private $concepts;

    public function __construct() {
        parent::__construct();
        $this->concepts = new PaymentConcept();
    }

    public function index() {
        $this->requireRole(['administrador']);
        $bank = new SchoolBankAccount();
        $this->view->render('concepts/index', [
            'concepts'    => $this->concepts->listWithUsage(),
            'bank'        => $bank->get(),
            'bankHistory' => $bank->recentHistory(6),
            'bankErrors'  => $_SESSION['bank_errors'] ?? [],
            'bankOld'     => $_SESSION['bank_old'] ?? null,
            'title'       => 'Conceptos de Cobro',
        ]);
        unset($_SESSION['bank_errors'], $_SESSION['bank_old']);   // se muestran una sola vez
    }

    /**
     * Guarda los datos bancarios de la escuela (los que ve el representante al reportar un pago). Solo administrador, POST
     * del mismo sitio (es informacion financiera: alterarla desviaria los pagos de las familias). La validacion y el historial
     * viven en SchoolBankAccount::save(); aqui solo se autentica y se responde. Si hay errores se vuelve al formulario con lo
     * que el usuario escribio, sin perderlo.
     */
    public function bank() {
        $this->requireRole(['administrador']);
        $this->requireSameOriginPost(false);
        $r = (new SchoolBankAccount())->save($_POST, [
            'id'   => (int)$_SESSION['admin_id'],
            'name' => (string)($_SESSION['admin_name'] ?? ''),
            'role' => (string)$_SESSION['admin_role'],
        ]);
        if (!$r['ok']) {
            $_SESSION['bank_errors'] = $r['errors'];
            $_SESSION['bank_old'] = array_intersect_key($_POST, SchoolBankAccount::FIELDS);
            $this->redirect('concepts?error=bank#datos-bancarios');
        }
        $this->redirect('concepts?success=' . ($r['changed'] ? 'bank_saved' : 'bank_same') . '#datos-bancarios');
    }

    /** Crea un concepto. POST: name, description, default_amount, is_monthly. */
    public function create() {
        $this->requireRole(['administrador']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('concepts');
        }
        [$data, $err] = $this->validate($_POST, null);
        if ($err) {
            $this->redirect('concepts?error=' . $err);
        }
        $data['created_by'] = (int)$_SESSION['admin_id'];
        $this->redirect($this->concepts->create($data) ? 'concepts?success=created' : 'concepts?error=failed');
    }

    /** Edita nombre, descripcion y tarifa. POST: id + campos. No toca pagos existentes. */
    public function update() {
        $this->requireRole(['administrador']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('concepts');
        }
        $id = (int)($_POST['id'] ?? 0);
        if (!$this->concepts->findById($id)) {
            $this->redirect('concepts?error=not_found');
        }
        [$data, $err] = $this->validate($_POST, $id);
        if ($err) {
            $this->redirect('concepts?error=' . $err);
        }
        $this->redirect($this->concepts->update($id, $data) ? 'concepts?success=updated' : 'concepts?error=failed');
    }

    /** Activa o desactiva un concepto (nunca se borra). POST: id, active=0|1. */
    public function toggle() {
        $this->requireRole(['administrador']);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('concepts');
        }
        $id = (int)($_POST['id'] ?? 0);
        if (!$this->concepts->findById($id)) {
            $this->redirect('concepts?error=not_found');
        }
        $this->concepts->update($id, ['is_active' => ($_POST['active'] ?? '') === '1' ? 1 : 0]);
        $this->redirect('concepts?success=updated');
    }

    /** @return array [datos|null, codigo_de_error|null] */
    private function validate(array $in, $excludeId) {
        $name = trim($in['name'] ?? '');
        $description = trim($in['description'] ?? '');
        $amount = round((float)str_replace(',', '.', (string)($in['default_amount'] ?? '0')), 2);

        if (mb_strlen($name) < 3 || mb_strlen($name) > 80) return [null, 'name'];
        if (mb_strlen($description) > 255)                  return [null, 'description'];
        if ($amount < 0 || $amount > 99999999)              return [null, 'amount'];
        if ($this->concepts->nameTaken($name, $excludeId))  return [null, 'duplicate'];

        return [[
            'name' => $name,
            'description' => $description !== '' ? $description : null,
            'default_amount' => $amount,
            'is_monthly' => !empty($in['is_monthly']) ? 1 : 0,
        ], null];
    }
}
