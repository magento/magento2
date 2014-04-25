<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    'preferences' => array(
        'Magento\Framework\Module\UpdaterInterface' => 'Magento\Framework\Module\Updaterter',
        'Magento\Framework\App\RequestInterface' => 'Magento\Framework\App\Request\Http\Proxy',
    ),
    'Magento\Framework\App\State' => array('arguments' => array('test name' => 'test value')),
    'Magento\Core\Model\Config\Modules' => array(
        'arguments' => array('test name' => 'test value'),
        'plugins' => array(
            'simple_modules_plugin' => array(
                'sortOrder' => 10,
                'disabled' => true,
                'instance' => 'Magento\Core\Model\Config\Modules\Plugin'
            ),
            'simple_modules_plugin_advanced' => array(
                'sortOrder' => 0,
                'instance' => 'Magento\Core\Model\Config\Modules\PluginAdvanced'
            ),
            'overridden_plugin' => array('sortOrder' => 30, 'disabled' => true)
        )
    ),
    'Magento\Framework\HTTP\Handler\Composite' => array(
        'shared' => false,
        'arguments' => array('test name' => 'test value')
    ),
    'customCacheInstance' => array(
        'shared' => true,
        'type' => 'Magento\Framework\App\Cache',
        'arguments' => array()
    ),
    'customOverriddenInstance' => array(
        'shared' => false,
        'arguments' => array()
    )
);
