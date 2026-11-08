<?php
/**
 * Vista de Inscripción y Registro de Atleta.
 * 
 * Formulario integral para dar de alta un nuevo atleta:
 * - Datos personales: Nombres, apellidos, cédula (si posee), fecha de nacimiento y cálculo automático de edad/categoría.
 * - Subida de fotografía del atleta con previsualización interactiva.
 * - Datos deportivos: Posición de juego, brazo de lanzar, lado de bateo, dorsal deseado.
 * - Ficha médica: Condiciones médicas, alergias, tipo de sangre, contacto de emergencia.
 * - Datos del Representante: Nombre, cédula, teléfono, correo electrónico y dirección.
 * - Generación automática de usuario de acceso para el representante y envío de planilla de inscripción.
 * 
 * Variables recibidas desde AthletesController::create():
 * @var array|null  $Athlete  Valores previamente enviados en caso de error.
 * @var string|null $error    Mensaje de validación o fallo de inserción.
 */
?>
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-user-plus text-red-700"></i>
                Nuevo Atleta
            </h2>
            <p class="text-gray-600 mt-1">Complete la información del atleta</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-6">
                <p class="text-red-600 text-sm"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-6">
            <!-- Información Personal -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información Personal</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                        <input 
                            type="text" 
                            name="first_name"
                            id="first_name"
                            required
                            value="<?php echo isset($Athlete['first_name']) ? htmlspecialchars($Athlete['first_name']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                       <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Apellido *</label>
                        <input 
                            type="text" 
                            name="last_name"
                            id="last_name"
                            required
                            value="<?php echo isset($Athlete['last_name']) ? htmlspecialchars($Athlete['last_name']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                       <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>

                    <div id="id_card_container">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cédula</label>
                        <input
                            type="text"
                            name="id_card"
                            id="id_card"
                            maxlength="8"
                            value="<?php echo isset($Athlete['id_card']) ? htmlspecialchars($Athlete['id_card']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                       <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Nacimiento *</label>
                        <input
                            type="date"
                            name="date_of_birth"
                            id="date_of_birth"
                            required
                            value="<?php echo isset($Athlete['date_of_birth']) ? htmlspecialchars($Athlete['date_of_birth']) : ''; ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Edad *</label>
                        <input 
                            type="number" 
                            name="age"
                            id="age"
                            min="5"
                            max="18"
                            required
                            value="<?php echo isset($Athlete['age']) ? htmlspecialchars($Athlete['age']) : ''; ?>"
                           readonly
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent bg-gray-100"
                       >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría *</label>
                        <select 
                            name="category"
                            id="category"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                        >
                            <option value="">Seleccionar categoría</option>
                            <option value="Infantil" data-min-age="8" data-max-age="10" <?php echo (isset($Athlete['category']) && $Athlete['category'] === 'Infantil') ? 'selected' : ''; ?>>Infantil (8-10 años)</option>
                            <option value="Pre-juvenil" data-min-age="11" data-max-age="12" <?php echo (isset($Athlete['category']) && $Athlete['category'] === 'Pre-juvenil') ? 'selected' : ''; ?>>Pre-juvenil (11-12 años)</option>
                            <option value="Juvenil" data-min-age="13" data-max-age="15" <?php echo (isset($Athlete['category']) && $Athlete['category'] === 'Juvenil') ? 'selected' : ''; ?>>Juvenil (13-15 años)</option>
                            <option value="Junior" data-min-age="16" data-max-age="18" <?php echo (isset($Athlete['category']) && $Athlete['category'] === 'Junior') ? 'selected' : ''; ?>>Junior (16-18 años)</option>
                        </select>
                        <p id="category-error" class="text-red-500 text-sm mt-1 hidden"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de Jersey *</label>
                        <input
                            type="number"
                            name="jersey_number"
                            min="1"
                            max="99"
                            required
                            value="<?php echo isset($Athlete['jersey_number']) ? htmlspecialchars($Athlete['jersey_number']) : ''; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fotografía</label>
                        <input
                            type="file"
                            name="photo"
                            id="photo"
                            accept="image/jpeg,image/jpg,image/png"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                       <p class="text-sm text-gray-500 mt-1">Formatos permitidos: JPG, JPEG, PNG. Tamaño máximo: 2MB</p>
                       <div id="photo-preview" class="mt-2 hidden">
                           <div class="w-32 h-32 rounded-lg overflow-hidden border">
                               <img src="" alt="Vista previa" class="w-full h-full object-cover">
                           </div>
                       </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estatus *</label>
                        <select 
                            id="status-select"
                            name="status" 
                            data-current="active"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                        >
                           <option value="active" <?php echo (isset($Athlete['status']) && $Athlete['status'] === 'active') ? 'selected' : ''; ?>>Activo</option>
                           <option value="inactive" <?php echo (isset($Athlete['status']) && $Athlete['status'] === 'inactive') ? 'selected' : ''; ?>>Inactivo</option>
                           <option value="suspended" <?php echo (isset($Athlete['status']) && $Athlete['status'] === 'suspended') ? 'selected' : ''; ?>>Suspendido</option>
                       </select>
                        <div id="status-reason-box" class="status-reason-field hidden mt-3">
                            <label for="status-reason" class="block text-sm font-medium text-gray-700 mb-1">Motivo del cambio *</label>
                            <textarea id="status-reason" name="status_reason" maxlength="500" placeholder="Ej: Se mudó de ciudad; falta disciplinaria; a solicitud del representante..."><?php echo htmlspecialchars($Athlete['status_reason'] ?? ''); ?></textarea>
                            <p class="text-xs text-gray-500 mt-1"><span id="status-reason-count">0 / 500</span> · Queda registrado con tu nombre y la fecha. Lo ven el administrador y el secretario.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del Padre/Madre -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información del Padre/Madre</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Padre/Madre *</label>
                        <input 
                            type="text" 
                            name="parent_name"
                            id="parent_name"
                            required
                            value="<?php echo isset($Athlete['parent_name']) ? htmlspecialchars($Athlete['parent_name']) : ''; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                       <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cédula del Padre/Madre *</label>
                        <input
                            type="text"
                            name="parent_id_card"
                            id="parent_id_card"
                            required
                            maxlength="8"
                            value="<?php echo isset($Athlete['parent_id_card']) ? htmlspecialchars($Athlete['parent_id_card']) : ''; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                       <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono *</label>
                        <div class="flex gap-2">
                            <select
                                name="parent_phone_code"
                                id="parent_phone_code"
                                required
                                class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                            >
                                <option value="0412" <?php echo (isset($Athlete['parent_phone']) && strpos($Athlete['parent_phone'], '0412') === 0) ? 'selected' : ''; ?>>0412</option>
                                <option value="0414" <?php echo (isset($Athlete['parent_phone']) && strpos($Athlete['parent_phone'], '0414') === 0) ? 'selected' : ''; ?>>0414</option>
                                <option value="0416" <?php echo (isset($Athlete['parent_phone']) && strpos($Athlete['parent_phone'], '0416') === 0) ? 'selected' : ''; ?>>0416</option>
                                <option value="0424" <?php echo (isset($Athlete['parent_phone']) && strpos($Athlete['parent_phone'], '0424') === 0) ? 'selected' : ''; ?>>0424</option>
                                <option value="0426" <?php echo (isset($Athlete['parent_phone']) && strpos($Athlete['parent_phone'], '0426') === 0) ? 'selected' : ''; ?>>0426</option>
                                <option value="0422" <?php echo (isset($Athlete['parent_phone']) && strpos($Athlete['parent_phone'], '0422') === 0) ? 'selected' : ''; ?>>0422</option>
                            </select>
                            <input
                                type="text"
                                name="parent_phone_number"
                                id="parent_phone_number"
                                required
                                placeholder="1234567"
                                maxlength="7"
                                pattern="[0-9]{7}"
                                value="<?php echo isset($Athlete['parent_phone']) ? htmlspecialchars(substr($Athlete['parent_phone'], -7)) : ''; ?>"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                            >
                            <input type="hidden" name="parent_phone" id="parent_phone" value="">
                        </div>
                        <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input 
                            type="email" 
                            name="parent_email"
                            id="parent_email"
                            required
                            value="<?php echo isset($Athlete['parent_email']) ? htmlspecialchars($Athlete['parent_email']) : ''; ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       >
                       <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                    </div>
                </div>
            </div>

            <!-- Información de Contacto -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información de Contacto</h3>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dirección *</label>
                        <textarea 
                            name="address" 
                            rows="2" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                       ><?php echo isset($Athlete['address']) ? htmlspecialchars($Athlete['address']) : ''; ?></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Contacto de Emergencia *</label>
                            <input 
                                type="text" 
                                name="emergency_contact"
                                id="emergency_contact"
                                required
                                value="<?php echo isset($Athlete['emergency_contact']) ? htmlspecialchars($Athlete['emergency_contact']) : ''; ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                           >
                           <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono de Emergencia *</label>
                            <div class="flex gap-2">
                                <select
                                    name="emergency_phone_code"
                                    id="emergency_phone_code"
                                    required
                                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                                >
                                    <option value="0412" <?php echo (isset($Athlete['emergency_phone']) && strpos($Athlete['emergency_phone'], '0412') === 0) ? 'selected' : ''; ?>>0412</option>
                                    <option value="0414" <?php echo (isset($Athlete['emergency_phone']) && strpos($Athlete['emergency_phone'], '0414') === 0) ? 'selected' : ''; ?>>0414</option>
                                    <option value="0416" <?php echo (isset($Athlete['emergency_phone']) && strpos($Athlete['emergency_phone'], '0416') === 0) ? 'selected' : ''; ?>>0416</option>
                                    <option value="0424" <?php echo (isset($Athlete['emergency_phone']) && strpos($Athlete['emergency_phone'], '0424') === 0) ? 'selected' : ''; ?>>0424</option>
                                    <option value="0426" <?php echo (isset($Athlete['emergency_phone']) && strpos($Athlete['emergency_phone'], '0426') === 0) ? 'selected' : ''; ?>>0426</option>
                                    <option value="0422" <?php echo (isset($Athlete['emergency_phone']) && strpos($Athlete['emergency_phone'], '0422') === 0) ? 'selected' : ''; ?>>0422</option>
                                </select>
                                <input
                                    type="text"
                                    name="emergency_phone_number"
                                    id="emergency_phone_number"
                                    required
                                    placeholder="1234567"
                                    maxlength="7"
                                    pattern="[0-9]{7}"
                                    value="<?php echo isset($Athlete['emergency_phone']) ? htmlspecialchars(substr($Athlete['emergency_phone'], -7)) : ''; ?>"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                                >
                                <input type="hidden" name="emergency_phone" id="emergency_phone" value="">
                            </div>
                            <p class="text-red-500 text-sm mt-1 hidden validation-error"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información Médica -->
            <div class="pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Información Médica</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Condiciones Médicas</label>
                    <textarea 
                        name="medical_conditions"
                        rows="3"
                        required
                        placeholder="Especificar cualquier condición médica relevante o escribir 'Ninguna'"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-700 focus:border-transparent"
                   ><?php echo isset($Athlete['medical_conditions']) ? htmlspecialchars($Athlete['medical_conditions']) : ''; ?></textarea>
                </div>
            </div>

            <!-- Botones -->
            <div class="flex gap-4 pt-6 border-t border-gray-200">
                <button 
                    type="submit" 
                    class="flex-1 btn-primary text-white font-medium py-3 px-4 rounded-lg transition-colors"
                >
                    <i class="fas fa-save mr-2"></i>
                    Crear Atleta
                </button>
                <a 
                    href="<?php echo BASE_URL; ?>athletes" 
                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-3 px-4 rounded-lg transition-colors text-center"
                >
                    <i class="fas fa-times mr-2"></i>
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    // --- Age & Category Validation ---
    const birthDateInput = document.getElementById('date_of_birth');
    const ageInput = document.getElementById('age');
    const categorySelect = document.getElementById('category');
    const categoryError = document.getElementById('category-error');
    const idCardContainer = document.getElementById('id_card_container');

    function calculateAge(birthDate) {
        const today = new Date();
        const birth = new Date(birthDate);
        let age = today.getFullYear() - birth.getFullYear();
        const m = today.getMonth() - birth.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
            age--;
        }
        return age;
    }

    function validateCategory() {
        const selectedCategory = categorySelect.options[categorySelect.selectedIndex];
        const category = selectedCategory.value;
        const age = parseInt(ageInput.value, 10);
        if (!category || !age) {
            categoryError.classList.add('hidden');
            return true;
        }
        const minAge = parseInt(selectedCategory.dataset.minAge, 10);
        const maxAge = parseInt(selectedCategory.dataset.maxAge, 10);
        if (age < minAge || age > maxAge) {
            categoryError.textContent = `La edad (${age}) no corresponde a la categoría "${category}" (${minAge}-${maxAge} años).`;
            categoryError.classList.remove('hidden');
            return false;
        }
        categoryError.classList.add('hidden');
        return true;
    }

    birthDateInput.addEventListener('input', function() {
        if (this.value) {
            const age = calculateAge(this.value);
            ageInput.value = age;
            validateCategory();
        }
    });
    categorySelect.addEventListener('input', validateCategory);

    // --- Phone Number Combination ---
    const parentPhoneCode = document.getElementById('parent_phone_code');
    const parentPhoneNumber = document.getElementById('parent_phone_number');
    const parentPhoneHidden = document.getElementById('parent_phone');
    
    const emergencyPhoneCode = document.getElementById('emergency_phone_code');
    const emergencyPhoneNumber = document.getElementById('emergency_phone_number');
    const emergencyPhoneHidden = document.getElementById('emergency_phone');

    function updateParentPhone() {
        if (parentPhoneCode && parentPhoneNumber && parentPhoneHidden) {
            parentPhoneHidden.value = parentPhoneCode.value + '-' + parentPhoneNumber.value;
        }
    }

    function updateEmergencyPhone() {
        if (emergencyPhoneCode && emergencyPhoneNumber && emergencyPhoneHidden) {
            emergencyPhoneHidden.value = emergencyPhoneCode.value + '-' + emergencyPhoneNumber.value;
        }
    }

    if (parentPhoneCode && parentPhoneNumber) {
        parentPhoneCode.addEventListener('change', updateParentPhone);
        parentPhoneNumber.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            updateParentPhone();
        });
        updateParentPhone();
    }

    if (emergencyPhoneCode && emergencyPhoneNumber) {
        emergencyPhoneCode.addEventListener('change', updateEmergencyPhone);
        emergencyPhoneNumber.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            updateEmergencyPhone();
        });
        updateEmergencyPhone();
    }

    // --- Field Format Validation ---
    const validations = {
        'first_name': { regex: /^[\p{L}\sñÑ]+$/u, message: 'El nombre solo debe contener letras y espacios.' },
        'last_name': { regex: /^[\p{L}\sñÑ]+$/u, message: 'El apellido solo debe contener letras y espacios.' },
        'parent_name': { regex: /^[\p{L}\sñÑ]+$/u, message: 'El nombre solo debe contener letras y espacios.' },
        'emergency_contact': { regex: /^[\p{L}\sñÑ]+$/u, message: 'El nombre solo debe contener letras y espacios.' },
        'id_card': { regex: /^[0-9]{8}$/, message: 'La cédula debe contener 8 dígitos.', optional: true },
        'parent_email': { regex: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, message: 'Por favor, introduce un email válido.' },
        'parent_phone_number': { regex: /^[0-9]{7}$/, message: 'Debe contener exactamente 7 dígitos.' },
        'emergency_phone_number': { regex: /^[0-9]{7}$/, message: 'Debe contener exactamente 7 dígitos.' },
        'parent_id_card': { regex: /^[0-9]{7,8}$/, message: 'La cédula debe contener entre 7 y 8 dígitos.' }
    };

    function validateField(input) {
        const rule = validations[input.id];
        if (!rule) return true;
        
        const errorElement = input.nextElementSibling;
        
        // Si el campo es opcional y está vacío, es válido
        if (rule.optional && input.value.trim() === '') {
            errorElement.classList.add('hidden');
            return true;
        }
        
        // Si el campo tiene contenido, validar formato
        if (input.value.trim() !== '' && !rule.regex.test(input.value)) {
            errorElement.textContent = rule.message;
            errorElement.classList.remove('hidden');
            return false;
        }
        
        errorElement.classList.add('hidden');
        return true;
    }

    Object.keys(validations).forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', () => validateField(input));
        }
    });

    // --- Form Submission ---
    form.addEventListener('submit', function(event) {
        let isFormValid = true;
        
        // Check category validation
        if (!validateCategory()) {
            isFormValid = false;
        }

        // Check all field validations (solo si tienen contenido o no son opcionales)
        Object.keys(validations).forEach(id => {
            const input = document.getElementById(id);
            const rule = validations[id];
            if (input) {
                // Solo validar si el campo tiene contenido o no es opcional
                if (!rule.optional || input.value.trim() !== '') {
                    if (!validateField(input)) {
                        isFormValid = false;
                    }
                }
            }
        });

        if (!isFormValid) {
            event.preventDefault();
            alert('Por favor, corrija los errores resaltados antes de enviar el formulario.');
        }

    // --- Photo Preview ---
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photo-preview');
    
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validar tamaño (2MB máximo)
                if (file.size > 2 * 1024 * 1024) {
                    alert('La imagen no debe superar los 2MB');
                    this.value = '';
                    photoPreview.classList.add('hidden');
                    return;
                }
                
                // Validar tipo
                if (!['image/jpeg', 'image/jpg', 'image/png'].includes(file.type)) {
                    alert('Solo se permiten imágenes JPG, JPEG o PNG');
                    this.value = '';
                    photoPreview.classList.add('hidden');
                    return;
                }
                
                // Mostrar vista previa
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoPreview.querySelector('img').src = e.target.result;
                    photoPreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                photoPreview.classList.add('hidden');
            }
        });
    }
    });
});
</script>

<script>
// Campo de motivo del estatus: visible solo al dejar al atleta Inactivo o Suspendido (y no si ya estaba asi).
// La validacion real es la del servidor; esto solo evita enviar el formulario sin motivo.
(function () {
    const select = document.getElementById('status-select');
    const box = document.getElementById('status-reason-box');
    const area = document.getElementById('status-reason');
    const count = document.getElementById('status-reason-count');
    if (!select || !box || !area) return;
    const current = select.getAttribute('data-current') || '';

    function sync() {
        const v = select.value;
        const needs = v !== 'active' && v !== current;
        box.classList.toggle('hidden', !needs);
        area.required = needs;
        if (!needs) { area.value = ''; }
        if (count) { count.textContent = area.value.length + ' / 500'; }
    }
    select.addEventListener('change', sync);
    area.addEventListener('input', function () { if (count) count.textContent = area.value.length + ' / 500'; });
    sync();
})();
</script>
