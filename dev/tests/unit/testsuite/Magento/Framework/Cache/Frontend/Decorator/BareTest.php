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
namespace Magento\Framework\Cache\Frontend\Decorator;

class BareTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $method
     * @param array $params
     * @param mixed $expectedResult
     * @dataProvider proxyMethodDataProvider
     */
    public function testProxyMethod($method, $params, $expectedResult)
    {
        $frontendMock = $this->getMock('Magento\Framework\Cache\FrontendInterface');

        $object = new \Magento\Framework\Cache\Frontend\Decorator\Bare($frontendMock);
        $helper = new \Magento\TestFramework\Helper\ProxyTesting();
        $result = $helper->invokeWithExpectations($object, $frontendMock, $method, $params, $expectedResult);
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function proxyMethodDataProvider()
    {
        return array(
            array('test', array('record_id'), 111),
            array('load', array('record_id'), '111'),
            array('save', array('record_value', 'record_id', array('tag'), 555), true),
            array('remove', array('record_id'), true),
            array('clean', array(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('tag')), true),
            array('getBackend', array(), $this->getMock('Zend_Cache_Backend')),
            array('getLowLevelFrontend', array(), $this->getMock('Zend_Cache_Core'))
        );
    }
}
