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
namespace Magento\Test\App\Arguments;

class ProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test, that object proxies methods and returns their results
     *
     * @param string $method
     * @param array $params
     * @dataProvider proxiedMethodsDataProvider
     */
    public function testProxiedMethods($method, $params)
    {
        $subject = $this->getMock('\Magento\Framework\App\Arguments', array(), array(), '', false);
        $invocation = $subject->expects($this->once())->method($method);
        $invocation = call_user_func_array(array($invocation, 'with'), $params);
        $expectedResult = new \stdClass();
        $invocation->will($this->returnValue($expectedResult));

        $object = new \Magento\TestFramework\App\Arguments\Proxy($subject);
        $actualResult = call_user_func_array(array($object, $method), $params);
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public static function proxiedMethodsDataProvider()
    {
        return array(
            array('getConnection', array('connection name')),
            array('getConnections', array()),
            array('getResources', array()),
            array('getCacheFrontendSettings', array()),
            array('getCacheTypeFrontendId', array('cache type')),
            array('get', array('key', 'default')),
            array('reload', array())
        );
    }

    public function testSetSubject()
    {
        $subject1 = $this->getMock('\Magento\Framework\App\Arguments', array(), array(), '', false);
        $subject1->expects($this->once())->method('get');

        $subject2 = $this->getMock('\Magento\Framework\App\Arguments', array(), array(), '', false);
        $subject2->expects($this->once())->method('get');

        $object = new \Magento\TestFramework\App\Arguments\Proxy($subject1);
        $object->get('data');

        $object->setSubject($subject2);
        $object->get('data');
    }
}
