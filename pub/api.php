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
 * @category    Mage
 * @package     Mage_Api2
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require dirname(__DIR__) . '/app/bootstrap.php';

if (!Mage::isInstalled()) {
    echo 'Application is not installed yet, please complete install wizard first.';
    exit;
}

// emulate index.php entry point for correct URLs generation in API
Mage::register('custom_entry_point', true);
Mage::$headersSentThrowsException = false;
Mage::init('admin');
Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_GLOBAL, Mage_Core_Model_App_Area::PART_EVENTS);
Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_ADMINHTML, Mage_Core_Model_App_Area::PART_EVENTS);

// query parameter "type" is set by .htaccess rewrite rule
$apiAlias = Mage::app()->getRequest()->getParam('type');

// check request could be processed by API2
if (in_array($apiAlias, Mage_Api2_Model_Server::getApiTypes())) {
    /** @var $server Mage_Api2_Model_Server */
    $server = Mage::getSingleton('Mage_Api2_Model_Server');

    $server->run();
} else {
    /* @var $server Mage_Api_Model_Server */
    $server = Mage::getSingleton('Mage_Api_Model_Server');
    $adapterCode = $server->getAdapterCodeByAlias($apiAlias);

    // if no adapters found in aliases - find it by default, by code
    if (null === $adapterCode) {
        $adapterCode = $apiAlias;
    }
    try {
        $server->initialize($adapterCode);
        $server->run();

        Mage::app()->getResponse()->sendResponse();
    } catch (Exception $e) {
        Mage::logException($e);

        echo $e->getMessage();
        exit;
    }
}
