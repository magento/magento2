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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\Di\Code\Scanner;

class XmlInterceptorScanner implements ScannerInterface
{
    /**
     * Get array of interceptor class names
     *
     * @param array $files
     * @return array
     */
    public function collectEntities(array $files)
    {
        $output = array();
        foreach ($files as $file) {
            $output = array_merge($output, $this->_collectEntitiesFromString(file_get_contents($file)));
        }
        $output = array_unique($output);
        $output = $this->_filterEntities($output);
        return $output;
    }

    /**
     * Collect entities from XML string
     *
     *
     * @param string $content
     * @return array
     */
    protected function _collectEntitiesFromString($content)
    {
        $output = array();
        $dom = new \DOMDocument();
        $dom->loadXML($content);
        $xpath = new \DOMXPath($dom);
        /** @var $entityNode \DOMNode */
        foreach ($xpath->query('//type[plugin]|//virtualType[plugin]') as $entityNode) {
            $attributes = $entityNode->attributes;
            $type = $attributes->getNamedItem('type');
            if (!is_null($type)) {
                array_push($output, $type->nodeValue);
            } else {
                array_push($output, $attributes->getNamedItem('name')->nodeValue);
            }
        }
        return $output;
    }

    /**
     * Filter found entities if needed
     *
     * @param array $output
     * @return array
     */
    protected function _filterEntities(array $output)
    {
        $filteredEntities = array();
        foreach ($output as $entityName) {
            // @todo the controller handling logic below must be removed when controllers become PSR-0 compliant
            $controllerSuffix = 'Controller';
            $pathParts = explode('_', $entityName);
            if (strrpos(
                $entityName,
                $controllerSuffix
            ) === strlen(
                $entityName
            ) - strlen(
                $controllerSuffix
            ) && isset(
                $pathParts[2]
            ) && !in_array(
                $pathParts[2],
                array('Block', 'Helper', 'Model')
            )
            ) {
                $this->_handleControllerClassName($entityName);
            }
            if (class_exists($entityName) || interface_exists($entityName)) {
                array_push($filteredEntities, $entityName . '\\Interceptor');
            }
        }
        return $filteredEntities;
    }

    /**
     * Include file with controller declaration if needed
     *
     * @param string $className
     * @return void
     */
    protected function _handleControllerClassName($className)
    {
        if (!class_exists($className)) {
            $className = preg_replace('/[^a-zA-Z0-9_]/', '', $className);
            $className = preg_replace('/^([0-9A-Za-z]*)_([0-9A-Za-z]*)/', '\\1_\\2_controllers', $className);
            $filePath = stream_resolve_include_path(str_replace('_', '/', $className) . '.php');
            if (file_exists($filePath)) {
                require_once $filePath;
            }
        }
    }
}
