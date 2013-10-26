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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
return array(
    'preferences' => array(
        'Magento\App\UpdaterInterface' => 'Magento\App\Updater',
        'Magento\Core\Model\AppInterface' => 'Magento\Core\Model\App\Proxy',
    ),

    'Magento\App\State' => array(
        'parameters' => array(
            'mode' => array(
                'argument' => 'MAGE_MODE',
            ),
        ),
    ),

    'Magento\Core\Model\Config_Modules' => array(
        'parameters' => array(
            'storage' => array(
                'instance' => 'Magento\Core\Model\Config\Storage_Modules',
                'shared' => false
            ),
        ),
        'plugins' => array(
            'simple_modules_plugin' => array(
                'sortOrder' => 10,
                'disabled' => true,
                'instance' => 'Magento\Core\Model\Config_Modules_Plugin',
            ),
            'simple_modules_plugin_advanced' => array(
                'sortOrder' => 0,
                'instance' => 'Magento\Core\Model\Config_Modules_PluginAdvanced',
            ),
            'overridden_plugin' => array(
                'sortOrder' => 30,
                'disabled' => true,
            ),
        ),
    ),

    'Magento\Http\Handler\Composite' => array(
        'shared' => false,
        'parameters' => array(
            'factory' => array(
                'instance' => 'Magento\Http\HandlerFactory',
            ),
            'handlers' => array(
                'custom_handler' => array(
                    'sortOrder' => 25,
                    'class' => 'Custom_Cache_Model_Http_Handler',
                ),
                'other_handler' => array(
                    'sortOrder' => 10,
                    'class' => 'Other_Cache_Model_Http_Handler',
                ),
            ),
        ),
    ),

    'Magento\Data\Collection\Db\FetchStrategy\Cache' => array(
        'parameters' => array(
            'cacheIdPrefix' => 'collection_',
            'cacheLifetime' => '86400',
            'cacheTags' => array(
                'const' => \Magento\Core\Model\Website::CACHE_TAG,
                'boolFalse' => false, 
                'boolTrue' => true,
                'boolOne' => true,
                'boolZero' => false,
                'intValue' => 100500,
                'nullValue' => null,
                'stringPattern' => 'az-value',
            ),
            'constParam' => 'website',
            'boolFalseParam' => false,
            'boolTrueParam' => true,
            'boolOneParam' => true,
            'boolZeroParam' => false,
            'intValueParam' => 100500,
            'nullValueParam' => null,
            'stringPatternParam' => 'az-value',
        ),
    ),

    'customCacheInstance' => array(
        'shared' => true,
        'type' => 'Magento\Core\Model\Cache',
        'parameters' => array(),
    ),

    'customOverriddenInstance' => array(
        'shared' => false,
        'parameters' => array(),
    ),
);
