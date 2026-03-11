/**
 * Модуль генерации гистограмм для статистики за последние 30 дней
 */
(function() {
    'use strict';

    /**
     * Категории статистики
     */
    const CATEGORIES = [
        { id: 'earnings', name: 'Заработок', class: 'bar-earnings', legendClass: 'legend-earnings' },
        { id: 'expenses', name: 'Траты', class: 'bar-expenses', legendClass: 'legend-expenses' },
        { id: 'investments', name: 'Инвестиции', class: 'bar-investments', legendClass: 'legend-investments' },
        { id: 'debts', name: 'Долги', class: 'bar-debts', legendClass: 'legend-debts' }
    ];
    
    /**
     * Кэш для хранения данных
     */
    let dataCache = {};
    
    /**
     * Получение краткого названия месяца с точкой
     * @param {number} monthIndex - Индекс месяца (0-11)
     * @returns {string} Краткое название месяца
     */
    function getShortMonthName(monthIndex) {
        const monthNames = ['янв.', 'фев.', 'мар.', 'апр.', 'май', 'июн.', 'июл.', 'авг.', 'сен.', 'окт.', 'ноя.', 'дек.'];
        return monthNames[monthIndex] || 'мес.';
    }
    
    /**
     * Форматирование валюты
     * @param {number} value - Значение для форматирования
     * @returns {string} Отформатированная строка
     */
    function formatCurrency(value) {
        return new Intl.NumberFormat('ru-RU', {
            style: 'currency',
            currency: 'RUB',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }
    
    /**
     * Форматирование числа для подписей на графике (максимум 3 цифры)
     * @param {number} value - Значение для форматирования
     * @returns {string} Отформатированная строка (например: "123 тыс.", "99 тыс.", "1,2 млн.")
     */
    function formatShortValue(value) {
        if (value >= 1000000) {
            // Для миллионов: показываем максимум 3 цифры
            const millions = value / 1000000;
            if (millions >= 100) {
                return Math.round(millions) + ' млн.';
            } else if (millions >= 10) {
                return Math.round(millions) + ' млн.';
            } else {
                return Math.round(millions * 10) / 10 + ' млн.';
            }
        } else if (value >= 1000) {
            // Для тысяч: показываем максимум 3 цифры
            const thousands = value / 1000;
            if (thousands >= 100) {
                return Math.round(thousands) + ' тыс.';
            } else if (thousands >= 10) {
                return Math.round(thousands) + ' тыс.';
            } else {
                return Math.round(thousands * 10) / 10 + ' тыс.';
            }
        } else {
            return Math.round(value).toString();
        }
    }
    
    /**
     * Генерация меток для последних 30 дней
     * Подписываем только каждый 5-й день (0, 5, 10, 15, 20, 25, 29)
     * @returns {Array<string>} Массив меток (даты, пустая строка для неподписанных дней)
     */
    function generateLast30DaysLabels() {
        const labels = [];
        const today = new Date();
        
        for (let i = 29; i >= 0; i--) {
            // Подписываем только каждый 5-й день (0, 5, 10, 15, 20, 25, 29)
            if (i % 5 === 0 || i === 29) {
                const date = new Date(today);
                date.setDate(date.getDate() - i);
                const day = date.getDate();
                const month = date.getMonth() + 1; // Месяц от 1 до 12
                labels.push(`${day}.${month.toString().padStart(2, '0')}`);
            } else {
                labels.push(''); // Пустая строка для неподписанных дней
            }
        }
        
        return labels;
    }
    
    /**
     * Получение данных для категории
     * @param {string} category - Категория
     * @param {number} intervals - Количество интервалов
     * @returns {Promise<Array<number>>} Промис с массивом данных
     */
    async function fetchCategoryData(category, intervals) {
        const cacheKey = `${category}_last30days_${intervals}`;
        
        // Проверяем кэш
        if (dataCache[cacheKey]) {
            return Promise.resolve(dataCache[cacheKey]);
        }
        
        try {
            const url = `/statistics/get-data?category=${category}&intervals=${intervals}`;
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error('Ошибка при получении данных: ' + response.status);
            }
            
            const data = await response.json();
            
            if (Array.isArray(data)) {
                dataCache[cacheKey] = data;
                return data;
            } else {
                const emptyData = new Array(intervals).fill(0);
                dataCache[cacheKey] = emptyData;
                return emptyData;
            }
        } catch (error) {
            const emptyData = new Array(intervals).fill(0);
            dataCache[cacheKey] = emptyData;
            return emptyData;
        }
    }
    
    /**
     * Получение всех данных для последних 30 дней
     * @param {number} intervals - Количество интервалов
     * @returns {Promise<Object>} Промис с объектом данных по категориям
     */
    async function fetchAllData(intervals) {
        const promises = CATEGORIES.map(category => 
            fetchCategoryData(category.id, intervals)
        );
        
        const results = await Promise.all(promises);
        const allData = {};
        
        CATEGORIES.forEach((category, index) => {
            allData[category.id] = results[index];
        });
        
        return allData;
    }
    
    /**
     * Создание одного столбца для категории
     * @param {number} value - Значение
     * @param {number} maxValue - Максимальное значение для масштабирования
     * @param {Object} category - Объект категории
     * @param {number} containerHeight - Высота контейнера в пикселях
     * @param {number} paddingTop - Padding-top контейнера в пикселях
     * @param {number} paddingBottom - Padding-bottom контейнера в пикселях
     * @returns {HTMLElement} Элемент столбца
     */
    function createBarElement(value, maxValue, category, maxHeight) {
        const bar = document.createElement('div');
        bar.className = 'chart-bar ' + category.class;
        
        // Используем переданный maxHeight для точного совпадения с линиями
        const heightPercent = maxValue > 0 ? (value / maxValue) * 100 : 0;
        const heightPx = (maxHeight * heightPercent) / 100;
        
        // Для нулевых значений делаем минимальную видимую полоску (2px)
        // Для ненулевых - минимум 5px, чтобы была визуальная разница с нулевыми
        const finalHeight = value > 0 ? Math.max(heightPx, 5) : 2;
        
        bar.style.height = finalHeight + 'px';
        bar.setAttribute('data-value', formatCurrency(value));
        bar.title = category.name + ': ' + formatCurrency(value);
        
        return bar;
    }
    
    /**
     * Создание группы столбцов для одного интервала
     * @param {Object} intervalData - Данные для интервала
     * @param {number} maxValue - Максимальное значение
     * @param {string} label - Метка интервала
     * @param {number} containerHeight - Высота контейнера в пикселях
     * @param {number} paddingTop - Padding-top контейнера в пикселях
     * @param {number} paddingBottom - Padding-bottom контейнера в пикселях
     * @returns {HTMLElement} Элемент группы
     */
    function createBarGroup(intervalData, maxValue, label, maxHeight) {
        const barGroup = document.createElement('div');
        barGroup.className = 'chart-bar-group';
        
        const barsContainer = document.createElement('div');
        barsContainer.className = 'chart-bars-container';
        
        CATEGORIES.forEach(category => {
            const value = intervalData[category.id] || 0;
            const bar = createBarElement(value, maxValue, category, maxHeight);
            barsContainer.appendChild(bar);
        });
        
        const labelElement = document.createElement('div');
        labelElement.className = 'chart-label';
        labelElement.textContent = label;
        
        barGroup.appendChild(barsContainer);
        barGroup.appendChild(labelElement);
        
        return barGroup;
    }
    
    /**
     * Создание горизонтальных линий и подписей для графика
     * @param {HTMLElement} container - Контейнер графика
     * @param {number} maxValue - Максимальное значение
     * @param {number} paddingLeft - Padding-left контейнера в пикселях
     * @param {number} maxHeight - Максимальная высота столбца в пикселях (75% от реальной высоты barsContainer)
     * @param {number} barsContainerTop - Позиция верха barsContainer от верха контейнера в пикселях
     * @param {number} actualBarsHeight - Реальная высота barsContainer в пикселях
     */
    function renderGridLines(container, maxValue, paddingLeft, maxHeight, barsContainerTop, actualBarsHeight) {
        // Удаляем старые линии и подписи, если есть
        const oldLines = container.querySelectorAll('.chart-grid-line');
        const oldLabels = container.querySelectorAll('.chart-y-label');
        oldLines.forEach(line => line.remove());
        oldLabels.forEach(label => label.remove());
        
        // Количество линий (обычно 4-5 для хорошей читаемости)
        const lineCount = 5;
        
        // Создаем линии и подписи
        // Подписи должны точно соответствовать линиям, а не средним значениям интервалов
        for (let i = 0; i <= lineCount; i++) {
            // Значение для линии (не среднее интервала, а точное значение на линии)
            const value = (maxValue * (lineCount - i)) / lineCount;
            
            // Позиция рассчитывается точно так же, как высота столбцов в createBarElement
            // heightPercent = (value / maxValue) * 100
            // heightPx = (maxHeight * heightPercent) / 100
            const heightPercent = maxValue > 0 ? (value / maxValue) * 100 : 0;
            const heightPx = (maxHeight * heightPercent) / 100;
            
            // Рассчитываем позицию верха столбца
            // Столбцы находятся внутри barsContainer и выровнены по низу (align-items: flex-end)
            // Позиция верха столбца от верха контейнера: barsContainerTop + (actualBarsHeight - heightPx)
            let topPosition;
            if (value === 0) {
                // Для нулевой линии: низ линии должен совпадать с низом столбцов
                // Низ столбцов находится на позиции barsContainerTop + actualBarsHeight
                // Линия имеет height: 1px, поэтому top = barsContainerTop + actualBarsHeight - 1
                topPosition = barsContainerTop + actualBarsHeight - 1;
            } else {
                // Для ненулевых линий: верх линии должен совпадать с верхом столбца
                topPosition = barsContainerTop + (actualBarsHeight - heightPx);
            }
            
            // Создаем горизонтальную линию
            const line = document.createElement('div');
            line.className = 'chart-grid-line';
            line.style.top = topPosition + 'px';
            line.style.left = paddingLeft + 'px';
            container.appendChild(line);
            
            // Создаем подпись слева (только для ненулевых значений)
            if (value > 0) {
                const label = document.createElement('div');
                label.className = 'chart-y-label';
                label.textContent = formatShortValue(value);
                // transform: translateY(-50%) центрирует подпись на линии
                label.style.top = topPosition + 'px';
                container.appendChild(label);
            }
        }
    }
    
    /**
     * Отрисовка легенды
     */
    function renderLegend() {
        const legendContainer = document.getElementById('last30days-legend');
        
        if (!legendContainer) {
            return;
        }
        
        legendContainer.innerHTML = '';
        
        CATEGORIES.forEach(category => {
            const legendItem = document.createElement('div');
            legendItem.className = 'legend-item';
            
            const colorBox = document.createElement('div');
            colorBox.className = 'legend-color ' + category.legendClass;
            
            const label = document.createElement('span');
            label.textContent = category.name;
            
            legendItem.appendChild(colorBox);
            legendItem.appendChild(label);
            legendContainer.appendChild(legendItem);
        });
    }
    
    /**
     * Отрисовка гистограммы для последних 30 дней
     * @param {HTMLElement} container - Контейнер для графика
     * @returns {Promise<void>} Промис завершения отрисовки
     */
    async function renderChart(container) {
        if (!container) {
            return;
        }
        
        container.innerHTML = '';
        
        const intervals = 30;
        const labels = generateLast30DaysLabels();
        const allData = await fetchAllData(intervals);
        
        // Получаем высоту контейнера
        const containerHeight = container.offsetHeight || 300;
        
        // Получаем реальные padding из computed styles
        const computedStyle = window.getComputedStyle(container);
        const paddingTop = parseFloat(computedStyle.paddingTop) || 16;
        const paddingBottom = parseFloat(computedStyle.paddingBottom) || 32;
        const paddingLeft = parseFloat(computedStyle.paddingLeft) || 64;
        
        // Вычисление максимального значения
        let maxValue = 1;
        CATEGORIES.forEach(category => {
            const categoryMax = Math.max(...allData[category.id], 0);
            if (categoryMax > maxValue) {
                maxValue = categoryMax;
            }
        });
        
        // Сохраняем данные для пересоздания столбцов
        const intervalsData = [];
        for (let i = 0; i < intervals; i++) {
            const intervalData = {};
            CATEGORIES.forEach(category => {
                intervalData[category.id] = allData[category.id][i] || 0;
            });
            intervalsData.push(intervalData);
        }
        
        // Вычисляем временную высоту для первоначального создания столбцов
        const availableHeight = containerHeight - paddingTop - paddingBottom;
        const tempMaxHeight = availableHeight * 0.75;
        
        // Создание гистограммы (сначала создаем столбцы с временным maxHeight, чтобы измерить реальную высоту)
        for (let i = 0; i < intervals; i++) {
            const barGroup = createBarGroup(intervalsData[i], maxValue, labels[i], tempMaxHeight);
            container.appendChild(barGroup);
        }
        
        // После создания столбцов измеряем реальную высоту области столбцов и пересчитываем все
        setTimeout(() => {
            const firstBarGroup = container.querySelector('.chart-bar-group');
            if (firstBarGroup) {
                const barsContainer = firstBarGroup.querySelector('.chart-bars-container');
                if (barsContainer) {
                    const actualBarsHeight = barsContainer.offsetHeight;
                    const barsContainerRect = barsContainer.getBoundingClientRect();
                    const containerRect = container.getBoundingClientRect();
                    const barsContainerTopFromContainerTop = barsContainerRect.top - containerRect.top;
                    
                    // Пересчитываем maxHeight на основе реальной высоты области столбцов
                    const realMaxHeight = actualBarsHeight * 0.75;
                    
                    // Пересоздаем все столбцы с правильным maxHeight
                    const allBarGroups = container.querySelectorAll('.chart-bar-group');
                    allBarGroups.forEach((barGroup, index) => {
                        const barsContainer = barGroup.querySelector('.chart-bars-container');
                        if (barsContainer && intervalsData[index]) {
                            barsContainer.innerHTML = '';
                            
                            // Создаем новые столбцы с правильным maxHeight
                            CATEGORIES.forEach(category => {
                                const value = intervalsData[index][category.id] || 0;
                                const bar = createBarElement(value, maxValue, category, realMaxHeight);
                                barsContainer.appendChild(bar);
                            });
                        }
                    });
                    
                    // Создаем линии с правильными расчетами
                    renderGridLines(container, maxValue, paddingLeft, realMaxHeight, barsContainerTopFromContainerTop, actualBarsHeight);
                }
            }
        }, 50);
        
        renderLegend();
    }
    
    /**
     * Инициализация графика
     */
    function initializeCharts() {
        const chartContainer = document.getElementById('last30days-chart');
        if (chartContainer && chartContainer.innerHTML.trim() === '') {
            renderChart(chartContainer);
        }
    }
    
    /**
     * Обновление графика
     */
    function updateChart() {
        const chartContainer = document.getElementById('last30days-chart');
        if (chartContainer) {
            dataCache = {};
            renderChart(chartContainer);
        }
    }
    
    /**
     * Публичный API
     */
    window.StatisticsCharts = {
        init: initializeCharts,
        updatePeriod: updateChart,
        clearCache: function() {
            dataCache = {};
        }
    };
    
    // Автоматическая инициализация
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeCharts);
    } else {
        initializeCharts();
    }
    
    // Поддержка PJAX (Yii2)
    if (typeof jQuery !== 'undefined' && jQuery.pjax) {
        jQuery(document).on('pjax:success', function() {
            dataCache = {};
            initializeCharts();
        });
    }
    
})();
