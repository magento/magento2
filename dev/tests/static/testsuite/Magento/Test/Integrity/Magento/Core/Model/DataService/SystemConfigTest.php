<?php
/**
 * Validates that all options with service_calls actually reference a valid service call
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity\Magento\Core\Model\DataService;

class SystemConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var array string[] */
    protected static $_serviceCalls = array();

    public static function setUpBeforeClass()
    {
        $configFiles = \Magento\TestFramework\Utility\Files::init()->getConfigFiles('service_calls.xml', array());
        foreach ($configFiles as $file) {
            $dom = new \DOMDocument();
            $dom->loadXML(file_get_contents($file[0]));
            $serviceCalls = $dom->getElementsByTagName('service_calls');
            $serviceCalls = $serviceCalls->item(0);
            if ($serviceCalls->hasChildNodes()) {
                foreach ($serviceCalls->childNodes as $serviceCall) {
                    /** @var $serviceCall \DOMNode */
                    if ($serviceCall->localName == 'service_call') {
                        self::$_serviceCalls[] = $serviceCall->attributes->getNamedItem('name')->nodeValue;
                    }
                }
            }
        }
    }

    public function testXmlFiles()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            function ($configFile) {
                $dom = new \DOMDocument();
                $dom->loadXML(file_get_contents($configFile));
                $this->assertNotNull($dom);
                $optionsList = $dom->getElementsByTagName('options');
                foreach ($optionsList as $options) {
                    /** @var $options \DOMNode */
                    if ($options->hasAttributes()) {
                        $serviceCallAttribute = $options->attributes->getNamedItem('service_call');
                        if (null != $serviceCallAttribute) {
                            $serviceCall = $serviceCallAttribute->nodeValue;
                            $this->assertTrue(
                                in_array($serviceCall, self::$_serviceCalls), "Unknown service call: $serviceCall"
                            );
                        }
                    }
                }
            },
            \Magento\TestFramework\Utility\Files::init()->getConfigFiles('adminhtml/system.xml', array())
        );
    }
}
