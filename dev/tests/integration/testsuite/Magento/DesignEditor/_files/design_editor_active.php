<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    'Magento\Framework\Config\ScopeInterface'
)->setCurrentScope(
    \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
);
$session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\DesignEditor\Model\Session');
/** @var $auth \Magento\Backend\Model\Auth */
$auth = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Backend\Model\Auth');
$auth->setAuthStorage($session);
$auth->login(\Magento\TestFramework\Bootstrap::ADMIN_NAME, \Magento\TestFramework\Bootstrap::ADMIN_PASSWORD);
$session->activateDesignEditor();

/** @var $theme \Magento\Framework\View\Design\ThemeInterface */
$theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Framework\View\Design\ThemeInterface'
);
$theme->setData(
    [
        'theme_code' => 'blank',
        'area' => 'frontend',
        'parent_id' => null,
        'theme_path' => 'Magento/blank',
        'theme_title' => 'Default',
        'preview_image' => 'media/preview_image.jpg',
        'is_featured' => '0',
    ]
);
$theme->save();
$session->setThemeId($theme->getId());
