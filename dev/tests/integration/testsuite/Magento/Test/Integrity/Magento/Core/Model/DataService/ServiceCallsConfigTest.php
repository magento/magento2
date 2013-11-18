<?php
/**
 * Find service_calls definitions and validate that name, service and method are present.
 *
 * Also validate that service is an existing class and the method exists on the service class.
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

class ServiceCallsConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider xmlDataProvider
     */
    public function testXmlFile($configFile, $dummy = false)
    {
        if (!$dummy) {
            $dom = new \DOMDocument();
            $dom->loadXML(file_get_contents($configFile));
            $this->assertNotNull($dom);
            $serviceCalls = $dom->getElementsByTagName('service_calls')->item(0);
            if ($serviceCalls->hasChildNodes()) {
                foreach ($serviceCalls->childNodes as $serviceCall) {
                    /** @var $serviceCall \DOMNode */
                    if ($serviceCall->localName == 'service_call') {
                        $name = $serviceCall->attributes->getNamedItem('name')->nodeValue;
                        $service = $serviceCall->attributes->getNamedItem('service')->nodeValue;
                        $method = $serviceCall->attributes->getNamedItem('method')->nodeValue;
                        try {
                            $ref = new \ReflectionClass($service);
                        } catch (\ReflectionException $re) {
                            $this->fail(
                                "$configFile has service_call $name with non-existent service class $service: $re"
                            );
                        }
                        $this->assertTrue(
                            $ref->hasMethod($method),
                            "$configFile has service_call $name invalid method $method"
                        );
                    }
                }
            }
        }
    }

    public function xmlDataProvider()
    {
        $files = \Magento\TestFramework\Utility\Files::init()->getConfigFiles('service_calls.xml', array());
        if (empty($files)) {
            $files = array(
                array('dummy', true)
            );
        }
        return $files;
    }
}

