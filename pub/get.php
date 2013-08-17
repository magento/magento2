<?php
/**
 * Public media files entry point
 *
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
 * @category   Mage
 * @package    Mage
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require dirname(__DIR__) . '/app/bootstrap.php';

$mediaDirectory = null;
$allowedResources = array();
$configCacheFile = dirname(__DIR__) . '/var/resource_config.json';
$relativeFilename = null;

$isAllowed = function ($resource, array $allowedResources) {
    $isResourceAllowed = false;
    foreach ($allowedResources as $allowedResource) {
        if (0 === stripos($resource, $allowedResource)) {
            $isResourceAllowed = true;
        }
    }
    return $isResourceAllowed;
};

if (file_exists($configCacheFile) && is_readable($configCacheFile)) {
    $config = json_decode(file_get_contents($configCacheFile), true);

    //checking update time
    if (filemtime($configCacheFile) + $config['update_time'] > time()) {
        $mediaDirectory = trim(str_replace(__DIR__, '', $config['media_directory']), DS);
        $allowedResources = array_merge($allowedResources, $config['allowed_resources']);
    }
}

// Serve file if it's materialized
$request = new Mage_Core_Model_File_Storage_Request(__DIR__);
if ($mediaDirectory) {
    if (0 !== stripos($request->getPathInfo(), $mediaDirectory . '/') || is_dir($request->getFilePath())) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    $relativeFilename = str_replace($mediaDirectory . '/', '', $request->getPathInfo());
    if (!$isAllowed($relativeFilename, $allowedResources)) {
        header('HTTP/1.0 404 Not Found');
        exit;
    }

    if (is_readable($request->getFilePath())) {
        $transfer = new Varien_File_Transfer_Adapter_Http();
        $transfer->send($request->getFilePath());
        exit;
    }
}
// Materialize file in application
$params = $_SERVER;
if (empty($mediaDirectory)) {
    $params[Mage::PARAM_ALLOWED_MODULES] = array('Mage_Core');
    $params[Mage::PARAM_CACHE_OPTIONS]['frontend_options']['disable_save'] = true;
}

$config = new Mage_Core_Model_Config_Primary(dirname(__DIR__), $params);
$entryPoint = new Mage_Core_Model_EntryPoint_Media(
    $config, $request, $isAllowed, __DIR__, $mediaDirectory, $configCacheFile, $relativeFilename
);
$entryPoint->processRequest();
