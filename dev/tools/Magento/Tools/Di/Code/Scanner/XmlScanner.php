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

class XmlScanner implements ScannerInterface
{
    /**
     * @var \Magento\Tools\Di\Compiler\Log\Log $log
     */
    protected $_log;

    /**
     * @param \Magento\Tools\Di\Compiler\Log\Log $log
     */
    public function __construct(\Magento\Tools\Di\Compiler\Log\Log $log)
    {
        $this->_log = $log;
    }

    /**
     * Get array of class names
     *
     * @param array $files
     * @return array
     */
    public function collectEntities(array $files)
    {
        $output = array();
        foreach ($files as $file) {
            $dom = new \DOMDocument();
            $dom->load($file);
            $xpath = new \DOMXPath($dom);
            $xpath->registerNamespace("php", "http://php.net/xpath");
            $xpath->registerPhpFunctions('preg_match');
            $regex = '/^(.*)\\\(.*)Proxy$/';
            $query = "/config/preference[ php:functionString('preg_match', '{$regex}', @type) > 0]/@type | " .
                "//argument[@xsi:type='object' and php:functionString('preg_match', '{$regex}', text()) > 0] |" .
                "//item[@xsi:type='object' and php:functionString('preg_match', '{$regex}', text()) > 0] |" .
                "/config/virtualType[ php:functionString('preg_match', '{$regex}', @type) > 0]/@type";
            /** @var \DOMNode $node */
            foreach ($xpath->query($query) as $node) {
                $output[] = $node->nodeValue;
            }
        }
        $output = array_unique($output);
        return $this->_filterEntities($output);
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
        foreach ($output as $className) {
            $entityName = substr($className, -6) === '\Proxy' ? substr($className, 0, -6) : $className;
            if (false === class_exists($className)) {
                if (class_exists($entityName) || interface_exists($entityName)) {
                    array_push($filteredEntities, $className);
                } else {
                    $this->_log->add(
                        \Magento\Tools\Di\Compiler\Log\Log::CONFIGURATION_ERROR,
                        $className,
                        'Invalid proxy class for ' . substr($className, 0, -5)
                    );
                }
            }
        }
        return $filteredEntities;
    }
}
