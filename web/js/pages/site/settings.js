/**
 * Модуль страницы настроек пользователя.
 * Управляет переключением тёмной/светлой темы через AJAX.
 *
 * @module SiteSettings
 */
(function () {
    'use strict';

    // ─── CONSTANTS / SELECTORS ─────────────────────────────────────────────

    var SELECTORS = {
        themeToggle: '.settings-theme-toggle',
    };

    var THEME_DARK  = 'dark';
    var THEME_LIGHT = 'light';

    var CSRF_TOKEN_META = 'meta[name="csrf-token"]';

    // ─── State ─────────────────────────────────────────────────────────────

    var isSaving = false;

    // ─── Utilities ─────────────────────────────────────────────────────────

    /**
     * Возвращает значение CSRF-токена из мета-тега Yii2.
     *
     * @returns {string}
     */
    function getCsrfToken() {
        var meta = document.querySelector(CSRF_TOKEN_META);
        return meta ? meta.getAttribute('content') : '';
    }

    /**
     * Применяет тему к корневому элементу <html>.
     *
     * @param {string} theme
     */
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
    }

    // ─── Main logic ────────────────────────────────────────────────────────

    /**
     * Отправляет тему на сервер через AJAX.
     *
     * @param {string} saveUrl
     * @param {string} theme
     * @param {HTMLInputElement} toggleEl
     */
    function saveTheme(saveUrl, theme, toggleEl) {
        if (isSaving) {
            return;
        }

        isSaving = true;
        toggleEl.disabled = true;

        fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-CSRF-Token': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: 'theme=' + encodeURIComponent(theme),
        })
            .then(function (response) {
                if (!response.ok) {
                    return response.json().then(function (data) {
                        throw new Error(data.message || 'Ошибка сервера: ' + response.status);
                    });
                }
                return response.json();
            })
            .then(function (data) {
                if (!data.success) {
                    throw new Error(data.message || 'Не удалось сохранить настройки.');
                }
            })
            .catch(function (err) {
                console.error('[SiteSettings] Ошибка сохранения темы:', err);
                var reverted = theme === THEME_DARK ? THEME_LIGHT : THEME_DARK;
                applyTheme(reverted);
                toggleEl.checked = !toggleEl.checked;
            })
            .finally(function () {
                isSaving = false;
                toggleEl.disabled = false;
            });
    }

    // ─── Event handlers ────────────────────────────────────────────────────

    /**
     * Обработчик переключения чекбокса темы.
     *
     * @param {Event} e
     */
    function onThemeToggleChange(e) {
        var toggleEl = e.target;
        var theme    = toggleEl.checked ? THEME_DARK : THEME_LIGHT;
        var saveUrl  = toggleEl.getAttribute('data-save-url');

        applyTheme(theme);
        saveTheme(saveUrl, theme, toggleEl);
    }

    // ─── Initialization ────────────────────────────────────────────────────

    /**
     * Привязывает обработчики событий, предотвращая дублирование.
     */
    function bindEvents() {
        var toggle = document.querySelector(SELECTORS.themeToggle);
        if (!toggle) {
            return;
        }

        toggle.removeEventListener('change', onThemeToggleChange);
        toggle.addEventListener('change', onThemeToggleChange);
    }

    /**
     * Инициализирует модуль.
     */
    function init() {
        bindEvents();
    }

    /**
     * Сбрасывает состояние и повторно инициализирует модуль (PJAX/AJAX).
     */
    function reinitialize() {
        isSaving = false;
        bindEvents();
    }

    // ─── Public API ────────────────────────────────────────────────────────

    window.SiteSettings = {
        init:         init,
        reinitialize: reinitialize,
    };

    // ─── Auto-init ─────────────────────────────────────────────────────────

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // ─── PJAX + AJAX reinitialize ──────────────────────────────────────────

    if (typeof jQuery !== 'undefined') {
        jQuery(document).on('pjax:success', reinitialize);
        jQuery(document).on('ajax:success', reinitialize);
    }

})();
