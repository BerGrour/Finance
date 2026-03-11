/**
 * Модуль формы расходов (трат).
 * Управляет отображением поля «Счет получателя» при выборе категории «Переводы».
 */
(function() {
    'use strict';

    // ============================================================================
    // КОНСТАНТЫ И КОНФИГУРАЦИЯ
    // ============================================================================

    /**
     * Селекторы элементов формы расходов
     */
    const SELECTORS = {
        form: '#expenses-form',
        categorySelect: '#expenses-category',
        transferAccountField: '#transfer-account-field',
        accountSelect: '#expenses-account_id',
        transferAccountSelect: '#transfer-account-select',
        isCountedInStats: '#expenses-is_counted_in_stats',
    };

    /**
     * Имя data-атрибута формы с кодом категории «Переводы»
     */
    const DATA_ATTR_CATEGORY_TRANSFER = 'categoryTransfer';

    // ============================================================================
    // УТИЛИТЫ
    // ============================================================================

    /**
     * Возвращает код категории «Переводы» из data-атрибута формы.
     *
     * @param {HTMLFormElement} form_el - Элемент формы
     * @returns {number} Код категории или 1 по умолчанию
     */
    function getCategoryTransferValue(form_el) {
        if (!form_el || !form_el.dataset) {
            return 1;
        }
        const raw = form_el.dataset[DATA_ATTR_CATEGORY_TRANSFER];
        const parsed = parseInt(raw, 10);
        return Number.isNaN(parsed) ? 1 : parsed;
    }

    // ============================================================================
    // ОСНОВНАЯ ЛОГИКА — ВИДИМОСТЬ ПОЛЯ ПЕРЕВОДА
    // ============================================================================

    /**
     * Показывает или скрывает блок выбора счёта получателя в зависимости от категории.
     *
     * @param {HTMLSelectElement} category_select - Селект категории
     * @param {HTMLElement} transfer_field - Блок поля «Счет получателя»
     * @param {number} category_transfer - Код категории «Переводы»
     */
    function toggleTransferField(category_select, transfer_field, category_transfer) {
        if (!category_select || !transfer_field) {
            return;
        }
        const selected_value = parseInt(category_select.value, 10);
        const is_transfer = selected_value === category_transfer;
        transfer_field.style.display = is_transfer ? 'block' : 'none';
    }

    // ============================================================================
    // ОСНОВНАЯ ЛОГИКА — АВТОМАТИЧЕСКОЕ УПРАВЛЕНИЕ «УЧИТЫВАТЬ В СТАТИСТИКЕ»
    // ============================================================================

    /**
     * Устанавливает состояние чекбокса «Учитывать в статистике» в зависимости
     * от выбранной категории. При переводе — снимает галочку, иначе — ставит.
     *
     * @param {HTMLSelectElement} category_select - Селект категории
     * @param {number} category_transfer - Код категории «Переводы»
     */
    function applyStatsCheckboxDefault(category_select, category_transfer) {
        const checkbox = document.querySelector(SELECTORS.isCountedInStats);
        if (!checkbox || !category_select) {
            return;
        }
        const is_transfer = parseInt(category_select.value, 10) === category_transfer;
        checkbox.checked = !is_transfer;
    }

    // ============================================================================
    // ОСНОВНАЯ ЛОГИКА — ВЗАИМНОЕ ИСКЛЮЧЕНИЕ СЧЕТОВ ПРИ ПЕРЕВОДЕ
    // ============================================================================

    /**
     * Читает все <option> из select-элемента и возвращает массив {id, text}.
     * Сохраняет пустой placeholder-option если он есть.
     *
     * @param {jQuery} $select - jQuery-обёртка над <select>
     * @returns {Array<{id: string, text: string}>}
     */
    function collectOptions($select) {
        const options = [];
        $select.find('option').each(function() {
            options.push({ id: jQuery(this).val(), text: jQuery(this).text() });
        });
        return options;
    }

    /**
     * Перестраивает <option> в select, исключая одно значение.
     * Если текущее выбранное значение совпало с исключаемым — сбрасывает выбор.
     *
     * @param {jQuery} $select - jQuery-обёртка над <select>
     * @param {Array<{id: string, text: string}>} all_options - Полный набор опций
     * @param {string|null} exclude_id - ID опции, которую нужно исключить
     */
    function rebuildSelectOptions($select, all_options, exclude_id) {
        const current_val = $select.val();
        $select.empty();

        all_options.forEach(function(opt) {
            if (opt.id === '' || opt.id !== String(exclude_id)) {
                $select.append(new Option(opt.text, opt.id, false, false));
            }
        });

        if (current_val && String(current_val) === String(exclude_id)) {
            $select.val(null).trigger('change.select2');
        } else {
            if (current_val) {
                $select.val(current_val);
            }
            $select.trigger('change.select2');
        }
    }

    /**
     * Инициализирует взаимное исключение одинакового счёта в двух Select2.
     * Актуально только при категории «Переводы».
     *
     * @param {HTMLSelectElement} category_select - Селект категории
     * @param {number} category_transfer - Код категории «Переводы»
     */
    function initAccountMutualExclusion(category_select, category_transfer) {
        if (typeof jQuery === 'undefined') {
            return;
        }

        const $account = jQuery(SELECTORS.accountSelect);
        const $transfer_account = jQuery(SELECTORS.transferAccountSelect);

        if (!$account.length || !$transfer_account.length) {
            return;
        }

        const account_all_options = collectOptions($account);
        const transfer_all_options = collectOptions($transfer_account);

        let is_syncing = false;

        function isTransferCategory() {
            return parseInt(category_select.value, 10) === category_transfer;
        }

        function onAccountChange() {
            if (is_syncing || !isTransferCategory()) {
                return;
            }
            is_syncing = true;
            rebuildSelectOptions($transfer_account, transfer_all_options, $account.val());
            is_syncing = false;
        }

        function onTransferAccountChange() {
            if (is_syncing || !isTransferCategory()) {
                return;
            }
            is_syncing = true;
            rebuildSelectOptions($account, account_all_options, $transfer_account.val());
            is_syncing = false;
        }

        $account.off('change.mutualExclusion').on('change.mutualExclusion', onAccountChange);
        $transfer_account.off('change.mutualExclusion').on('change.mutualExclusion', onTransferAccountChange);

        // Применить начальное состояние при редактировании
        if (isTransferCategory()) {
            const initial_account_val = $account.val();
            const initial_transfer_val = $transfer_account.val();

            if (initial_account_val) {
                is_syncing = true;
                rebuildSelectOptions($transfer_account, transfer_all_options, initial_account_val);
                is_syncing = false;
            }
            if (initial_transfer_val) {
                is_syncing = true;
                rebuildSelectOptions($account, account_all_options, initial_transfer_val);
                is_syncing = false;
            }
        }
    }

    // ============================================================================
    // ИНИЦИАЛИЗАЦИЯ И УПРАВЛЕНИЕ
    // ============================================================================

    /**
     * Инициализация обработчиков событий формы расходов.
     */
    function initEventHandlers() {
        const form_el = document.querySelector(SELECTORS.form);
        const category_select = document.querySelector(SELECTORS.categorySelect);
        const transfer_field = document.querySelector(SELECTORS.transferAccountField);

        if (!form_el || !category_select || !transfer_field) {
            return;
        }

        const category_transfer = getCategoryTransferValue(form_el);

        /** Обработчик изменения категории */
        function handleCategoryChange() {
            toggleTransferField(category_select, transfer_field, category_transfer);
            applyStatsCheckboxDefault(category_select, category_transfer);
        }

        category_select.removeEventListener('change', handleCategoryChange);
        category_select.addEventListener('change', handleCategoryChange);

        // Начальное состояние при загрузке (в т.ч. при редактировании)
        toggleTransferField(category_select, transfer_field, category_transfer);

        initAccountMutualExclusion(category_select, category_transfer);
    }

    /**
     * Инициализация модуля формы расходов.
     */
    function init() {
        initEventHandlers();
    }

    /**
     * Переинициализация после AJAX/PJAX (повторная привязка обработчиков).
     */
    function reinitialize() {
        init();
    }

    // ============================================================================
    // ЭКСПОРТ ПУБЛИЧНОГО API
    // ============================================================================

    /**
     * Публичный API модуля формы расходов
     */
    window.ExpensesForm = {
        init: init,
        reinitialize: reinitialize,
    };

    // ============================================================================
    // АВТОМАТИЧЕСКАЯ ИНИЦИАЛИЗАЦИЯ
    // ============================================================================

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Поддержка PJAX (Yii2)
    if (typeof jQuery !== 'undefined' && jQuery.pjax) {
        jQuery(document).on('pjax:success', function() {
            reinitialize();
        });
    }

    // Поддержка общих AJAX-обновлений
    document.addEventListener('ajax:success', function() {
        reinitialize();
    });

})();
