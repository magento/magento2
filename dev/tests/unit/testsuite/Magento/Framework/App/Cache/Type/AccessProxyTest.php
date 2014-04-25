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
namespace Magento\Framework\App\Cache\Type;

class AccessProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $method
     * @param array $params
     * @param bool $disabledResult
     * @param mixed $enabledResult
     * @dataProvider proxyMethodDataProvider
     */
    public function testProxyMethod($method, $params, $disabledResult, $enabledResult)
    {
        $identifier = 'cache_type_identifier';

        $frontendMock = $this->getMock('Magento\Framework\Cache\FrontendInterface');

        $cacheEnabler = $this->getMock('Magento\Framework\App\Cache\StateInterface');
        $cacheEnabler->expects($this->at(0))->method('isEnabled')->with($identifier)->will($this->returnValue(false));
        $cacheEnabler->expects($this->at(1))->method('isEnabled')->with($identifier)->will($this->returnValue(true));

        $object = new \Magento\Framework\App\Cache\Type\AccessProxy($frontendMock, $cacheEnabler, $identifier);
        $helper = new \Magento\TestFramework\Helper\ProxyTesting();

        // For the first call the cache is disabled - so fake default result is returned
        $result = $helper->invokeWithExpectations($object, $frontendMock, $method, $params, $enabledResult);
        $this->assertSame($disabledResult, $result);

        // For the second call the cache is enabled - so real cache result is returned
        $result = $helper->invokeWithExpectations($object, $frontendMock, $method, $params, $enabledResult);
        $this->assertSame($enabledResult, $result);
    }

    /**
     * @return array
     */
    public static function proxyMethodDataProvider()
    {
        return array(
            array('test', array('record_id'), false, 111),
            array('load', array('record_id'), false, '111'),
            array('save', array('record_value', 'record_id', array('tag'), 555), true, false),
            array('remove', array('record_id'), true, false),
            array('clean', array(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('tag')), true, false)
        );
    }
}
