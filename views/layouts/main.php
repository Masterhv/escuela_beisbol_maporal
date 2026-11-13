<?php
/**
 * Plantilla Principal (Layout Maestro) de la Aplicación.
 * 
 * Estructura común para todas las vistas web del sistema:
 * - Carga los metadatos, hoja de estilos unificada (style.css) y scripts globales (Alpine.js).
 * - Renderiza la barra superior de marca y usuario activo con botón de salida.
 * - Renderiza el menú de navegación principal con control de visibilidad según rol:
 *   * Dashboard, Atletas, Pagos, Entrenamientos, Torneos, Inventario, Reportes, Administradores.
 * - Soporta navegación responsiva para dispositivos móviles.
 * - Modal y temporizador de cierre de sesión automático por inactividad (10 minutos).
 * - Helpers JavaScript globales: ajaxRequest(), initAutocomplete(), etc.
 * 
 * Variables globales disponibles:
 * @var string|null $title    Título de la página actual.
 * @var string      $content  Contenido HTML de la vista hija a incrustar dentro del <main>.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>
    
    <!-- Hoja de estilos única (Tailwind, Font Awesome, iconos, colores y estilos propios) -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>views/assets/css/style.css?v=<?php echo @filemtime(BASE_PATH . 'views/assets/css/style.css') ?: VERSION; ?>">

    <!-- Alpine.js -->
    <script defer src="<?php echo BASE_URL; ?>views/assets/js/alpine.min.js?v=<?php echo @filemtime(BASE_PATH . 'views/assets/js/alpine.min.js') ?: VERSION; ?>"></script>
</head>
<body class="bg-gray-50">
    <?php if (isset($_SESSION['admin_id'])): ?>
        <!-- Navigation -->
        <div class="bg-white shadow-lg sticky top-0 z-40">

            <!-- Top bar: brand + user -->
            <div class="border-b border-gray-100">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">

                        <!-- Brand -->
                        <div class="flex items-center gap-3">
                            <img src="<?php echo BASE_URL; ?>img/logo-maporal.png" alt="Logo Maporal" class="h-10 w-auto flex-shrink-0">
                            <div class="leading-tight">
                                <p class="text-xs font-semibold text-red-700 uppercase tracking-widest">Sistema de Gestión</p>
                                <h1 class="text-base font-bold text-gray-900 whitespace-nowrap">Escuela de Béisbol Menor Maporal</h1>
                            </div>
                        </div>

                        <!-- User + logout -->
                        <div class="flex items-center gap-4">
                            <div class="hidden sm:block text-right">
                                <p class="text-sm font-semibold text-gray-900 leading-tight"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></p>
                                <p class="text-xs text-gray-400 capitalize"><?php echo htmlspecialchars($_SESSION['admin_role']); ?></p>
                            </div>
                            <a href="<?php echo BASE_URL; ?>auth/logout"
                               class="flex items-center gap-1.5 px-3 py-1.5 text-sm text-gray-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors border border-transparent hover:border-red-200">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="hidden sm:inline">Salir</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom bar: navigation -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">

                    <nav class="hidden md:flex">
                        <?php
                        // Menu y permisos definidos UNA sola vez; el menu movil reutiliza $navItems.
                        // 'roles' = quienes lo ven (omitido = todos). 'href' = destino (por defecto, el id).
                        // La visibilidad del menu es solo comodidad: cada controlador vuelve a validar el rol.
                        $role = $_SESSION['admin_role'];
                        $navAll = [
                            ['id' => 'dashboard',   'label' => 'Dashboard',        'icon' => 'fa-home'],
                            ['id' => 'athletes',    'label' => ($role === 'representante' ? 'Mis Atletas' : 'Atletas'), 'icon' => 'fa-users'],
                            ['id' => 'payments',    'label' => ($role === 'representante' ? 'Mis pagos' : 'Pagos'), 'icon' => 'fa-credit-card', 'roles' => ['administrador', 'secretario', 'representante']],
                            ['id' => 'review',      'label' => 'Por revisar',      'icon' => 'fa-clipboard-check', 'href' => 'payments/review', 'roles' => ['secretario', 'administrador']],
                            ['id' => 'concepts',    'label' => 'Conceptos',        'icon' => 'fa-tags',            'roles' => ['administrador']],
                            ['id' => 'training',    'label' => 'Entrenamientos',   'icon' => 'fa-calendar-alt'],
                            ['id' => 'tournaments', 'label' => 'Torneos',          'icon' => 'fa-trophy'],
                            ['id' => 'inventory',   'label' => 'Inventario',       'icon' => 'fa-boxes', 'roles' => ['administrador', 'secretario', 'entrenador']],
                            ['id' => 'reports',     'label' => 'Reportes',         'icon' => 'fa-file-alt', 'roles' => ['administrador', 'secretario', 'entrenador']],
                            ['id' => 'admins',      'label' => 'Administradores',  'icon' => 'fa-cog',   'roles' => ['administrador']],
                        ];
                        $navItems = array_values(array_filter($navAll, fn($i) => !isset($i['roles']) || in_array($role, $i['roles'], true)));

                        // Contador de "Por revisar": pagos En Revision que ESTE usuario (secretario o administrador, que
                        // concilian por igual) puede conciliar; excluye los que el mismo registro. Si falla la consulta no se
                        // muestra nada: el menu nunca debe romper una pagina.
                        $navBadges = [];
                        if (in_array($role, ['secretario', 'administrador'], true)) {
                            try {
                                require_once 'models/PaymentWorkflow.php';
                                $navBadges['review'] = (new PaymentWorkflow())->countReviewableBy($_SESSION['admin_id']);
                            } catch (\Throwable $e) {
                                error_log('[menu] No se pudo calcular el contador de pagos por revisar: ' . $e->getMessage());
                            }
                        }
                        // HTML de la insignia de un item (vacio si no hay nada que mostrar).
                        $navBadge = function ($item) use ($navBadges) {
                            $n = (int)($navBadges[$item['id']] ?? 0);
                            if ($n <= 0) return '';
                            $txt = $n > 99 ? '99+' : (string)$n;
                            return "<span class='nav-badge' title='$n en revisión'>{$txt}</span>";
                        };
                        $activeSection = $this->getActiveSection();
                        // 'review' vive bajo payments/: se resalta por la accion, no por el controlador.
                        $activeAction = strtolower(basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
                        $isNavActive = function ($item) use ($activeSection, $activeAction) {
                            if ($item['id'] === 'review') {
                                return $activeSection === 'payments' && $activeAction === 'review';
                            }
                            if ($item['id'] === 'payments') {
                                return $activeSection === 'payments' && $activeAction !== 'review';
                            }
                            return $activeSection === $item['id'];
                        };

                        foreach ($navItems as $item) {
                            $isActive   = $isNavActive($item);
                            $activeClass = $isActive
                                ? 'border-b-2 border-red-700 text-red-700 font-semibold'
                                : 'border-b-2 border-transparent text-gray-600 hover:text-red-700 hover:border-red-300';
                            echo "<a href='" . BASE_URL . ($item['href'] ?? $item['id']) . "' class='flex items-center gap-1.5 px-3 py-3 text-sm font-medium transition-colors whitespace-nowrap {$activeClass}'>
                                    <i class='fas {$item['icon']} text-xs'></i>
                                    {$item['label']}
                                    " . $navBadge($item) . "
                                  </a>";
                        }
                        ?>
                    </nav>
                    <!-- spacer -->
                    <div></div>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div class="md:hidden border-t border-gray-200">
                <div class="flex overflow-x-auto">
                    <?php
                    foreach ($navItems as $item) {
                        $isActive = $isNavActive($item);
                        $activeClass = $isActive ? 'bg-red-50 text-red-800 font-semibold' : 'text-black hover:text-red-800 hover:bg-gray-100';
                        echo "<a href='" . BASE_URL . ($item['href'] ?? $item['id']) . "' class='nav-item-mobile flex flex-col items-center gap-1 px-4 py-3 text-xs font-medium whitespace-nowrap transition-colors {$activeClass}'>
                                <i class='fas {$item['icon']} w-4 h-4'></i>
                                {$item['label']}
                                " . $navBadge($item) . "
                              </a>";
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <?php echo $content; ?>
        </main>
        
    <?php else: ?>
        <!-- Content for non-authenticated users -->
        <div class="min-h-screen">
            <?php echo $content; ?>
        </div>
    <?php endif; ?>

    <!-- Inactivity Modal -->
    <div id="inactivity-modal" class="fixed inset-0 bg-gray-800 bg-opacity-75 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md text-center">
            <h3 class="text-lg font-bold text-gray-900">Su sesión está a punto de expirar</h3>
            <p class="text-black mt-2">Por inactividad, su sesión se cerrará en <span id="countdown-timer" class="font-bold">30</span> segundos.</p>
            <p class="text-black mt-1">¿Desea continuar en el sistema?</p>
            <div class="mt-6 flex justify-center gap-4">
                <button id="logout-now-btn" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Salir
                </button>
                <button id="keep-session-btn" class="btn-primary text-white font-bold py-2 px-4 rounded">
                    Mantener sesión activa
                </button>
            </div>
        </div>
    </div>
    
    <!-- JavaScript -->
    <script>
        // AJAX helper
        function ajaxRequest(url, data, callback) {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(data => callback(data))
            .catch(function(error) {
                console.error('Error:', error);
                callback({ success: false, message: 'Error de conexión. Intente nuevamente.' });
            });
        }

        // Autocomplete helper mejorado
        function initAutocomplete(inputId, resultsId, endpointUrl, resultFormatter, hiddenInputId = null, onSelectCallback = null) {
            const input = document.getElementById(inputId);
            const resultsContainer = document.getElementById(resultsId);
            const hiddenInput = hiddenInputId ? document.getElementById(hiddenInputId) : null;

            if (!input || !resultsContainer) {
                console.error('initAutocomplete: No se encontraron elementos', {inputId, resultsId});
                return;
            }

            console.log('Autocomplete inicializado para:', inputId);

            let debounceTimer;

            input.addEventListener('input', function(e) {
                clearTimeout(debounceTimer);
                const term = this.value.trim();

                console.log('Input event, term:', term);

                if (hiddenInput) {
                    hiddenInput.value = '';
                }

                if (term.length < 2) {
                    resultsContainer.innerHTML = '';
                    resultsContainer.classList.add('hidden');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    console.log('Buscando:', term, 'en:', endpointUrl);
                    
                    fetch(`${endpointUrl}?term=${encodeURIComponent(term)}`)
                        .then(response => {
                            console.log('Response status:', response.status);
                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log('Datos recibidos:', data);
                            resultsContainer.innerHTML = '';
                            
                            if (data && data.length > 0) {
                                resultsContainer.classList.remove('hidden');
                                data.forEach(result => {
                                    const formatted = resultFormatter(result, term);
                                    const { id, text, html } = formatted;

                                    const item = document.createElement('div');
                                    item.className = 'px-3 py-2 hover:bg-red-50 cursor-pointer border-b border-gray-100 transition-colors';

                                    if (html) {
                                        // El formatter provee HTML personalizado (con foto, etc.)
                                        item.innerHTML = html;
                                    } else {
                                        // Resaltar coincidencias en texto plano
                                        try {
                                            const escapedTerm = term.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                                            const regex = new RegExp(`(${escapedTerm})`, 'gi');
                                            const highlightedText = text.replace(regex, '<strong class="text-red-700">$1</strong>');
                                            item.innerHTML = highlightedText;
                                        } catch (e) {
                                            item.textContent = text;
                                        }
                                    }

                                    item.dataset.id = id;
                                    item.dataset.text = text;

                                    item.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();

                                        input.value = this.dataset.text;
                                        if (hiddenInput) {
                                            hiddenInput.value = this.dataset.id;
                                        }
                                        resultsContainer.innerHTML = '';
                                        resultsContainer.classList.add('hidden');

                                        if (onSelectCallback && typeof onSelectCallback === 'function') {
                                            onSelectCallback(result);
                                        }
                                    });

                                    resultsContainer.appendChild(item);
                                });
                            } else {
                                resultsContainer.innerHTML = '<div class="p-3 text-black text-sm text-center">No se encontraron resultados</div>';
                                resultsContainer.classList.remove('hidden');
                            }
                        })
                        .catch(error => {
                            console.error('Error en búsqueda:', error);
                            resultsContainer.innerHTML = `<div class="p-3 text-red-500 text-sm">Error: ${error.message}</div>`;
                            resultsContainer.classList.remove('hidden');
                        });
                }, 300);
            });

            // Cerrar al hacer clic fuera
            document.addEventListener('click', function(e) {
                if (e.target !== input && !resultsContainer.contains(e.target)) {
                    resultsContainer.classList.add('hidden');
                }
            });
        }

        // Resalta las coincidencias del término en un texto
        function highlightTerm(text, term) {
            if (!term) return text;
            try {
                const escaped = term.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                return text.replace(new RegExp(`(${escaped})`, 'gi'), '<strong class="search-highlight">$1</strong>');
            } catch(e) {
                return text;
            }
        }

        // Genera el formatter con foto para búsquedas de atletas
        function athleteResultFormatter(result, baseUrl, term) {
            const name = `${result.first_name} ${result.last_name}`.trim();
            const ci  = result.id_card       ? `CI Atl: ${result.id_card}`       : '';
            const rep = result.parent_id_card ? `CI Rep: ${result.parent_id_card}` : '';
            const cedulaInfo = [ci, rep].filter(x => x).join(' — ');
            const text = `${name} — ${cedulaInfo}`;

            const noPhoto = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 40 40'%3E%3Ccircle cx='20' cy='20' r='20' fill='%23e5e7eb'/%3E%3Ccircle cx='20' cy='16' r='7' fill='%239ca3af'/%3E%3Cellipse cx='20' cy='36' rx='12' ry='8' fill='%239ca3af'/%3E%3C/svg%3E`;
            // La foto se pide por el id del atleta al endpoint autenticado (athletes/photo): el archivo ya no es accesible por su ruta.
            // result.photo solo indica SI el atleta tiene foto; su ruta interna no se usa.
            const photoSrc = result.photo
                ? `${baseUrl}athletes/photo?id=${encodeURIComponent(result.id)}`
                : noPhoto;

            const html = `<div class="flex items-center gap-3">
                <img src="${photoSrc}" alt="${name}"
                     onerror="this.src='${noPhoto}'"
                     class="autocomplete-photo">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">${highlightTerm(name, term)}</p>
                    <p class="text-xs text-gray-500 truncate">${highlightTerm(cedulaInfo, term)}</p>
                </div>
            </div>`;

            return { id: result.id, text, html };
        }

        function setupIdCardAutocomplete(inputId, resultsId) {
            initAutocomplete(
                inputId,
                resultsId,
                '<?php echo BASE_URL; ?>athletes/autocomplete',
                function(result) {
                    return {
                        id: result.id,
                        text: `${result.id_card} - ${result.first_name} ${result.last_name}`
                    };
                }
            );
        }
 
         // Inactivity Logout Logic
        <?php if (isset($_SESSION['admin_id'])): ?>
        (function() {
            const inactivityTime = 120; // 2 minutes (120 seconds)
            const warningTime = 30; // 30 seconds warning
            let inactivityTimer, warningTimer, countdownInterval;
            let modalVisible = false;

            const modal = document.getElementById('inactivity-modal');
            const countdownElement = document.getElementById('countdown-timer');
            const keepSessionBtn = document.getElementById('keep-session-btn');
            const logoutNowBtn = document.getElementById('logout-now-btn');

            function showWarning() {
                modalVisible = true;
                modal.classList.remove('hidden');
                let countdown = warningTime;
                countdownElement.textContent = countdown;

                countdownInterval = setInterval(() => {
                    countdown--;
                    countdownElement.textContent = countdown;
                    if (countdown <= 0) {
                        clearInterval(countdownInterval);
                        logout();
                    }
                }, 1000);

                warningTimer = setTimeout(logout, warningTime * 1000);
            }

            function logout() {
                window.location.href = '<?php echo BASE_URL; ?>auth/logout';
            }

            function resetTimers() {
                // Don't reset timers if modal is visible
                if (modalVisible) {
                    return;
                }
                
                clearTimeout(inactivityTimer);
                clearTimeout(warningTimer);
                clearInterval(countdownInterval);
                modal.classList.add('hidden');
                
                inactivityTimer = setTimeout(showWarning, (inactivityTime - warningTime) * 1000);
            }

            function keepSession() {
                modalVisible = false;
                clearTimeout(warningTimer);
                clearInterval(countdownInterval);
                modal.classList.add('hidden');
                
                ajaxRequest('<?php echo BASE_URL; ?>auth/keepAlive', {}, function(response) {
                    if (response.success) {
                        // Start fresh timer after successful keep alive
                        inactivityTimer = setTimeout(showWarning, (inactivityTime - warningTime) * 1000);
                    } else {
                        logout();
                    }
                });
            }

            // Event listeners for user activity
            function handleUserActivity() {
                resetTimers();
            }

            window.addEventListener('load', resetTimers);
            document.addEventListener('mousemove', handleUserActivity);
            document.addEventListener('keypress', handleUserActivity);
            document.addEventListener('scroll', handleUserActivity);
            document.addEventListener('click', handleUserActivity);

            keepSessionBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                keepSession();
            });
            
            logoutNowBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                logout();
            });
        })();
        <?php endif; ?>

        // Bloqueo contra el doble envio de formularios: al enviar un formulario nativo se desactivan
        // sus botones submit, para que un doble clic (o un reintento con respuesta lenta) no cree el
        // registro dos veces. Si otra validacion cancela el envio con preventDefault se dejan libres,
        // y al volver atras desde la cache del navegador (bfcache) se reactivan para poder reintentar.
        (function() {
            var guardButtons = function(form) {
                return form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]');
            };

            document.addEventListener('submit', function(e) {
                var form = e.target;
                if (!(form instanceof HTMLFormElement) || form.dataset.guardSubmit !== undefined) return;
                form.dataset.guardSubmit = '1';
                setTimeout(function() {
                    if (e.defaultPrevented) {
                        delete form.dataset.guardSubmit;
                        return;
                    }
                    guardButtons(form).forEach(function(btn) {
                        if (btn.disabled) return;
                        btn.disabled = true;
                        btn.dataset.guardLocked = '1';
                    });
                }, 0);
            });

            window.addEventListener('pageshow', function(e) {
                if (!e.persisted) return;
                document.querySelectorAll('form[data-guard-submit]').forEach(function(form) {
                    delete form.dataset.guardSubmit;
                    guardButtons(form).forEach(function(btn) {
                        if (btn.dataset.guardLocked === '1') {
                            btn.disabled = false;
                            delete btn.dataset.guardLocked;
                        }
                    });
                });
            });
        })();
    </script>
</body>
</html>