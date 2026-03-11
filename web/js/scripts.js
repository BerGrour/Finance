/**
 * Главная точка входа JS.
 * Здесь подключаются и инициализируются глобальные модули.
 */
(function() {
    'use strict';

    /**
     * Инициализация глобальных модулей, доступных на разных страницах.
     */
    function initGlobalModules() {
        if (window.StatisticsCharts && typeof window.StatisticsCharts.init === 'function') {
            window.StatisticsCharts.init();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGlobalModules);
    } else {
        initGlobalModules();
    }

    if (typeof jQuery !== 'undefined' && jQuery.pjax) {
        jQuery(document).on('pjax:success', function() {
            initGlobalModules();
        });
    }
})();

