<?php

use yii\helpers\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\models\User\User;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Html::tag('div', 
            Html::tag('span', '💰', ['class' => 'navbar-logo']) . 
            Html::tag('span', Yii::$app->name, ['class' => 'navbar-brand-text d-none d-md-inline']),
            ['class' => 'navbar-brand d-flex align-items-center']
        ),
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-expand-lg navbar-dark fixed-top',
        ],
    ]);
    
    // Правая часть: меню вкладок, уведомления, пользователь
    $rightMenuItems = [];
    
    if (!Yii::$app->user->isGuest) {
        // DropDown с вкладками (иконка)
        $menuItems = [
            ['label' => 'Главная', 'url' => ['/site/index']],
            ['label' => 'Заработок', 'url' => ['/earnings/index']],
            ['label' => 'Траты', 'url' => ['/expenses/index']],
            ['label' => 'Инвестиции', 'url' => ['/investments/index']],
            ['label' => 'Долги', 'url' => ['/debts/index']],
            ['label' => 'Статистика', 'url' => ['/statistics/index']],
        ];
        
        $rightMenuItems[] = '<li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarMenuDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarMenuDropdown">
                ' . implode('', array_map(function($item) {
                    return '<li><a class="dropdown-item" href="' . \yii\helpers\Url::to($item['url']) . '">' . Html::encode($item['label']) . '</a></li>';
                }, $menuItems)) . '
            </ul>
        </li>';
        
        // Уведомления (заглушка)
        $rightMenuItems[] = '<li class="nav-item">
            <a class="nav-link" href="#" title="Уведомления">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                    <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
                </svg>
            </a>
        </li>';
        
        // Пользователь DropDown
        $userMenuItems = [
            ['label' => 'Профиль', 'url' => ['/site/profile']],
            ['label' => 'Настройки', 'url' => ['/site/settings']],
            ['label' => 'Счета', 'url' => ['/accounts/index']],
            ['label' => 'divider'],
            ['label' => 'Выход', 'url' => ['/site/logout'], 'linkOptions' => ['data-method' => 'post']],
        ];
        
        /** @var \app\models\User\User|null $user */
        $user = Yii::$app->user->identity;
        $username = $user && $user instanceof User ? $user->username : 'Пользователь';
        $rightMenuItems[] = '<li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="me-2">' . Html::encode($username) . '</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                    <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                    <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                </svg>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarUserDropdown">
                ' . implode('', array_map(function($item) {
                    if ($item['label'] === 'divider') {
                        return '<li><hr class="dropdown-divider"></li>';
                    }
                    $linkOptions = isset($item['linkOptions']) ? $item['linkOptions'] : [];
                    $method = isset($linkOptions['data-method']) ? ' data-method="' . $linkOptions['data-method'] . '"' : '';
                    return '<li><a class="dropdown-item" href="' . \yii\helpers\Url::to($item['url']) . '"' . $method . '>' . Html::encode($item['label']) . '</a></li>';
                }, $userMenuItems)) . '
            </ul>
        </li>';
    } else {
        $rightMenuItems[] = ['label' => 'Вход', 'url' => ['/site/login']];
        $rightMenuItems[] = ['label' => 'Регистрация', 'url' => ['/site/signup']];
    }
    
    // Выводим элементы
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav ms-auto'],
        'items' => $rightMenuItems,
    ]);
    
    NavBar::end();
    ?>

    <div class="container">
        <?php 
        $breadcrumbs = isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [];
        if (!empty($breadcrumbs) && !empty($this->title)) {
            $lastBreadcrumb = end($breadcrumbs);
            $lastLabel = is_array($lastBreadcrumb) ? $lastBreadcrumb['label'] : $lastBreadcrumb;
            if ($lastLabel === $this->title) {
                array_pop($breadcrumbs);
            }
        }
        if (!empty($breadcrumbs)): ?>
            <?= Breadcrumbs::widget([
                'links' => $breadcrumbs,
                'homeLink' => [
                    'label' => 'Главная',
                    'url' => Yii::$app->homeUrl,
                ],
                'options' => ['class' => 'breadcrumb'],
                'itemTemplate' => "<li class=\"breadcrumb-item\">{link}</li>\n",
                'activeItemTemplate' => "<li class=\"breadcrumb-item active\" aria-current=\"page\">{link}</li>\n",
            ]) ?>
        <?php endif; ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-0">&copy; <?= Html::encode(Yii::$app->name) ?> <?= date('Y') ?></p>
            </div>
            <div class="col-md-6 text-md-end">
                <ul class="list-inline mb-0">
                    <li class="list-inline-item">
                        <?= Html::a('О нас', ['/site/about'], ['class' => 'text-decoration-none']) ?>
                    </li>
                    <li class="list-inline-item">|</li>
                    <li class="list-inline-item">
                        <?= Html::a('Контакты', ['/site/contact'], ['class' => 'text-decoration-none']) ?>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
