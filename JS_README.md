# Структура JavaScript файлов

## Общая структура

```
web/js/
├── scripts.js      # Файл общих скриптов
├── main/           # Общие модули (используются на всех страницах)
│   ├── charts.js       # Модуль для генерации графиков статистики
└── pages/          # Страничные скрипты (специфичные для конкретных страниц)
    ├── site/           # Скрипты для контроллера SiteController
    │   └── index.js    # Скрипт для главной страницы
    ├── expences/        # Скрипты для Expences представлений
    └── ...             # Другие директории по сущностям
```

## Директория `main/`

Содержит общие JavaScript модули, которые используются на всех или многих страницах приложения.
**Загружаются автоматически** через `AppAsset` на всех страницах.

### `charts.js`
Модуль для генерации гистограмм статистики. Предоставляет API:
- `StatisticsCharts.init()` - инициализация всех графиков
- `StatisticsCharts.updatePeriod(period)` - обновление графика для периода
- `StatisticsCharts.reinitialize()` - переинициализация (для AJAX/PJAX)
- `StatisticsCharts.clearCache()` - очистка кэша данных

## Директория `pages/`

Содержит JavaScript файлы, специфичные для конкретных страниц приложения. Организована по контроллерам.

## Правила добавления новых скриптов

1. **Страничные скрипты**:
   - Создать директорию с именем контроллера в `pages/` (например, `pages/account/`)
   - Создать файл с именем action (например, `index.js` для `actionIndex()`)
   - Зарегистрировать через `registerJsFile()` в соответствующем view файле

2. **Общие модули**:
   - Разместить в `main/` с понятным именем
   - Добавить в `AppAsset.php` для автоматической загрузки на всех страницах (при необходимости)

3. **Регистрация**:
   - Использовать `registerJsFile()` вместо `registerJs()` для встроенного кода
   - Указать зависимости через параметр `depends`



# Документация по JavaScript модулям

Данная документация описывает структуру и паттерны создания модульных JavaScript скриптов для проекта Finance.

## Содержание

1. [Общая структура модуля](#общая-структура-модуля)
2. [Паттерны и лучшие практики](#паттерны-и-лучшие-практики)
3. [Поддержка AJAX/PJAX](#поддержка-ajaxpjax)
4. [Примеры создания модулей](#примеры-создания-модулей)
5. [API и публичные методы](#api-и-публичные-методы)

---

## Общая структура модуля

Каждый модуль должен следовать следующей структуре:

```javascript
/**
 * Описание модуля
 * Дополнительная информация о назначении
 */
(function() {
    'use strict';
    
    // ============================================================================
    // КОНСТАНТЫ И КОНФИГУРАЦИЯ
    // ============================================================================
    
    /**
     * Константы модуля
     */
    const CONSTANTS = {
        KEY_NAME: 'value',
        SELECTOR: '#element-id'
    };
    
    /**
     * Селекторы элементов
     */
    const SELECTORS = {
        container: '.container',
        button: '.button'
    };
    
    /**
     * Кэш для хранения данных (если необходимо)
     */
    let dataCache = {};
    
    // ============================================================================
    // УТИЛИТЫ
    // ============================================================================
    
    /**
     * Вспомогательные функции
     * @param {type} param - Описание параметра
     * @returns {type} Описание возвращаемого значения
     */
    function utilityFunction(param) {
        // Реализация
    }
    
    // ============================================================================
    // ИНИЦИАЛИЗАЦИЯ И УПРАВЛЕНИЕ
    // ============================================================================
    
    /**
     * Инициализация модуля
     */
    function init() {
        // Инициализация
    }
    
    /**
     * Переинициализация (для AJAX/PJAX)
     */
    function reinitialize() {
        // Переинициализация
    }
    
    // ============================================================================
    // ОБРАБОТЧИКИ СОБЫТИЙ
    // ============================================================================
    
    /**
     * Инициализация обработчиков событий
     */
    function initEventHandlers() {
        // Обработчики
    }
    
    // ============================================================================
    // ЭКСПОРТ ПУБЛИЧНОГО API
    // ============================================================================
    
    /**
     * Публичный API для внешнего использования
     */
    window.ModuleName = {
        init: init,
        reinitialize: reinitialize,
        // Другие публичные методы
    };
    
    // ============================================================================
    // АВТОМАТИЧЕСКАЯ ИНИЦИАЛИЗАЦИЯ
    // ============================================================================
    
    // Инициализация при загрузке DOM
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
    
    // Поддержка общих AJAX обновлений
    document.addEventListener('ajax:success', function() {
        reinitialize();
    });
    
})();
```

---

## Паттерны и лучшие практики

### 1. Использование IIFE (Immediately Invoked Function Expression)

Все модули должны быть обернуты в IIFE для изоляции области видимости:

```javascript
(function() {
    'use strict';
    // Код модуля
})();
```

### 2. Организация кода по секциям

Код должен быть разделен на логические секции с комментариями:

- **КОНСТАНТЫ И КОНФИГУРАЦИЯ** - константы, селекторы, кэш
- **УТИЛИТЫ** - вспомогательные функции
- **ОСНОВНАЯ ЛОГИКА** - основные функции модуля
- **ИНИЦИАЛИЗАЦИЯ И УПРАВЛЕНИЕ** - функции инициализации
- **ОБРАБОТЧИКИ СОБЫТИЙ** - обработчики событий
- **ЭКСПОРТ ПУБЛИЧНОГО API** - публичные методы
- **АВТОМАТИЧЕСКАЯ ИНИЦИАЛИЗАЦИЯ** - автоматический запуск

### 3. Использование констант

Все магические числа и строки должны быть вынесены в константы:

### 4. Селекторы в константах

Все селекторы должны быть вынесены в объект `SELECTORS`:

```javascript
const SELECTORS = {
    container: '#myContainer',
    button: '.my-button',
    input: '[data-input]'
};
```

### 5. JSDoc комментарии

Все функции должны иметь JSDoc комментарии:

```javascript
/**
 * Краткое описание функции
 * 
 * @param {string} param1 - Описание параметра 1
 * @param {number} param2 - Описание параметра 2
 * @returns {boolean} Описание возвращаемого значения
 */
function myFunction(param1, param2) {
    // Реализация
}
```

### 6. Проверка существования элементов

Всегда проверять существование элементов перед работой с ними:

```javascript
const element = document.querySelector(SELECTORS.container);
if (!element) {
    return; // или console.warn('Element not found');
}
```

### 7. Делегирование событий

Делегирование событий для элементов, которые могут быть добавлены динамически:

```javascript
document.addEventListener('click', function(e) {
    if (e.target.matches(SELECTORS.button)) {
        handleClick(e.target);
    }
});
```

---

## Поддержка AJAX/PJAX

### Автоматическая переинициализация

Модули должны автоматически переинициализироваться после AJAX/PJAX обновлений:

```javascript
// Поддержка PJAX (Yii2)
if (typeof jQuery !== 'undefined' && jQuery.pjax) {
    jQuery(document).on('pjax:success', function() {
        reinitialize();
    });
}

// Поддержка общих AJAX обновлений
document.addEventListener('ajax:success', function() {
    reinitialize();
});
```

### Функция reinitialize()

Должна очищать кэш и переинициализировать модуль:

```javascript
function reinitialize() {
    clearDataCache();
    init();
}
```

### Кэширование данных

Использование кэша для оптимизации и с его очисткой:

```javascript
let dataCache = {};

function clearDataCache() {
    dataCache = {};
}

function getCachedData(key) {
    if (dataCache[key]) {
        return dataCache[key];
    }
    // Получение данных
    const data = fetchData();
    dataCache[key] = data;
    return data;
}
```

---

## Примеры создания модулей

### Пример 1: Простой модуль с обработчиками событий

```javascript
/**
 * Модуль управления формой
 */
(function() {
    'use strict';
    
    const SELECTORS = {
        form: '#myForm',
        submitButton: '[type="submit"]',
        input: 'input[required]'
    };
    
    function validateForm(form) {
        const inputs = form.querySelectorAll(SELECTORS.input);
        let isValid = true;
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('error');
            } else {
                input.classList.remove('error');
            }
        });
        
        return isValid;
    }
    
    function handleSubmit(e) {
        e.preventDefault();
        const form = e.target;
        
        if (validateForm(form)) {
            // Отправка формы
            submitForm(form);
        }
    }
    
    function initEventHandlers() {
        const form = document.querySelector(SELECTORS.form);
        if (form) {
            form.addEventListener('submit', handleSubmit);
        }
    }
    
    function init() {
        initEventHandlers();
    }
    
    window.FormModule = {
        init: init
    };
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();
```

### Пример 2: Модуль с асинхронными операциями

```javascript
/**
 * Модуль загрузки данных
 */
(function() {
    'use strict';
    
    const SELECTORS = {
        container: '#dataContainer',
        loadButton: '#loadData'
    };
    
    let dataCache = {};
    
    async function fetchData(url) {
        const cacheKey = url;
        
        if (dataCache[cacheKey]) {
            return Promise.resolve(dataCache[cacheKey]);
        }
        
        try {
            const response = await fetch(url);
            const data = await response.json();
            dataCache[cacheKey] = data;
            return data;
        } catch (error) {
            console.error('Error fetching data:', error);
            throw error;
        }
    }
    
    async function loadAndDisplayData(url) {
        const container = document.querySelector(SELECTORS.container);
        if (!container) return;
        
        container.innerHTML = 'Загрузка...';
        
        try {
            const data = await fetchData(url);
            renderData(container, data);
        } catch (error) {
            container.innerHTML = 'Ошибка загрузки данных';
        }
    }
    
    function renderData(container, data) {
        // Отрисовка данных
        container.innerHTML = JSON.stringify(data);
    }
    
    function initEventHandlers() {
        const button = document.querySelector(SELECTORS.loadButton);
        if (button) {
            button.addEventListener('click', function() {
                loadAndDisplayData('/api/data');
            });
        }
    }
    
    function clearCache() {
        dataCache = {};
    }
    
    function init() {
        initEventHandlers();
    }
    
    function reinitialize() {
        clearCache();
        init();
    }
    
    window.DataLoader = {
        init: init,
        reinitialize: reinitialize,
        clearCache: clearCache
    };
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Поддержка PJAX
    if (typeof jQuery !== 'undefined' && jQuery.pjax) {
        jQuery(document).on('pjax:success', reinitialize);
    }
    
})();
```

### Пример 3: Модуль с публичным API

```javascript
/**
 * Модуль управления модальными окнами
 */
(function() {
    'use strict';
    
    const SELECTORS = {
        modal: '.modal',
        openButton: '[data-modal-open]',
        closeButton: '[data-modal-close]'
    };
    
    function openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }
    
    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }
    
    function closeAllModals() {
        const modals = document.querySelectorAll(SELECTORS.modal);
        modals.forEach(modal => {
            modal.classList.remove('show');
        });
        document.body.style.overflow = '';
    }
    
    function initEventHandlers() {
        // Делегирование для кнопок открытия
        document.addEventListener('click', function(e) {
            if (e.target.matches(SELECTORS.openButton)) {
                const modalId = e.target.getAttribute('data-modal-open');
                openModal(modalId);
            }
        });
        
        // Делегирование для кнопок закрытия
        document.addEventListener('click', function(e) {
            if (e.target.matches(SELECTORS.closeButton)) {
                const modal = e.target.closest(SELECTORS.modal);
                if (modal) {
                    closeModal(modal.id);
                }
            }
        });
        
        // Закрытие по клику вне модального окна
        document.addEventListener('click', function(e) {
            if (e.target.matches(SELECTORS.modal)) {
                closeModal(e.target.id);
            }
        });
    }
    
    function init() {
        initEventHandlers();
    }
    
    function reinitialize() {
        init();
    }
    
    // Публичный API
    window.ModalManager = {
        init: init,
        reinitialize: reinitialize,
        open: openModal,
        close: closeModal,
        closeAll: closeAllModals
    };
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    if (typeof jQuery !== 'undefined' && jQuery.pjax) {
        jQuery(document).on('pjax:success', reinitialize);
    }
    
})();
```

---

## API и публичные методы

### Рекомендуемая структура публичного API

```javascript
window.ModuleName = {
    // Инициализация
    init: init,
    
    // Переинициализация
    reinitialize: reinitialize,
    
    // Основные методы
    method1: function() { ... },
    method2: function() { ... },
    
    // Утилиты
    clearCache: clearCache,
    destroy: destroy
};
```

### Использование публичного API

```javascript
// Инициализация модуля
StatisticsCharts.init();

// Переинициализация после AJAX
StatisticsCharts.reinitialize();

// Использование методов
ModalManager.open('myModal');
ModalManager.close('myModal');
```

## Рекомендации по именованию

### Имена модулей

- PascalCase для публичного API: `StatisticsCharts`, `ModalManager`
- camelCase для внутренних функций: `initEventHandlers`, `renderChart`

### Имена констант

- UPPER_SNAKE_CASE: `MAX_VALUE`, `DEFAULT_TIMEOUT`
- Для объектов PascalCase: `SELECTORS`, `CONFIG`

### Имена селекторов

- Описательные имена: `container`, `submitButton`, `modalClose`

---

## Обработка ошибок

Всегда обрабатывать возможные ошибки:

```javascript
async function fetchData(url) {
    try {
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error('Error fetching data:', error);
        // Возврат значения по умолчанию или проброс ошибки
        throw error;
    }
}
```

---

## Тестирование модулей

Для тестирования модулей публичный API:

```javascript
// Тест инициализации
StatisticsCharts.init();

// Тест переинициализации
StatisticsCharts.reinitialize();

// Тест очистки кэша
StatisticsCharts.clearCache();
```

## Примеры существующих модулей

В проекте уже реализованы следующие модули:

1. **charts.js** - Модуль генерации гистограмм для статистики
2. **statistics.js** - Модуль управления блюром статистики
