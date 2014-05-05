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
namespace Magento\Framework\Cache\Frontend\Adapter;

class ZendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $method
     * @param array $params
     * @param array $expectedParams
     * @param mixed $expectedResult
     * @dataProvider proxyMethodDataProvider
     */
    public function testProxyMethod($method, $params, $expectedParams, $expectedResult)
    {
        $frontendMock = $this->getMock('Zend_Cache_Core');
        $object = new \Magento\Framework\Cache\Frontend\Adapter\Zend($frontendMock);
        $helper = new \Magento\TestFramework\Helper\ProxyTesting();
        $result = $helper->invokeWithExpectations(
            $object,
            $frontendMock,
            $method,
            $params,
            $expectedResult,
            $method,
            $expectedParams
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return array
     */
    public function proxyMethodDataProvider()
    {
        return array(
            'test' => array('test', array('record_id'), array('RECORD_ID'), 111),
            'load' => array('load', array('record_id'), array('RECORD_ID'), '111'),
            'save' => array(
                'save',
                array('record_value', 'record_id', array('tag1', 'tag2'), 555),
                array('record_value', 'RECORD_ID', array('TAG1', 'TAG2'), 555),
                true
            ),
            'remove' => array('remove', array('record_id'), array('RECORD_ID'), true),
            'clean mode "all"' => array(
                'clean',
                array(\Zend_Cache::CLEANING_MODE_ALL, array()),
                array(\Zend_Cache::CLEANING_MODE_ALL, array()),
                true
            ),
            'clean mode "matching tag"' => array(
                'clean',
                array(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('tag1', 'tag2')),
                array(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('TAG1', 'TAG2')),
                true
            ),
            'clean mode "matching any tag"' => array(
                'clean',
                array(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('tag1', 'tag2')),
                array(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('TAG1', 'TAG2')),
                true
            ),
            'getBackend' => array(
                'getBackend',
                array(),
                array(),
                $this->getMock('Zend_Cache_Backend')
            )
        );
    }

    /**
     * @param string $cleaningMode
     * @param string $expectedErrorMessage
     * @dataProvider cleanExceptionDataProvider
     */
    public function testCleanException($cleaningMode, $expectedErrorMessage)
    {
        $this->setExpectedException('InvalidArgumentException', $expectedErrorMessage);
        $object = new \Magento\Framework\Cache\Frontend\Adapter\Zend($this->getMock('Zend_Cache_Core'));
        $object->clean($cleaningMode);
    }

    public function cleanExceptionDataProvider()
    {
        return array(
            'cleaning mode "expired"' => array(
                \Zend_Cache::CLEANING_MODE_OLD,
                "Magento cache frontend does not support the cleaning mode 'old'."
            ),
            'cleaning mode "not matching tag"' => array(
                \Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
                "Magento cache frontend does not support the cleaning mode 'notMatchingTag'."
            ),
            'non-existing cleaning mode' => array(
                'nonExisting',
                "Magento cache frontend does not support the cleaning mode 'nonExisting'."
            )
        );
    }

    public function testGetLowLevelFrontend()
    {
        $frontendMock = $this->getMock('Zend_Cache_Core');
        $object = new \Magento\Framework\Cache\Frontend\Adapter\Zend($frontendMock);
        $this->assertSame($frontendMock, $object->getLowLevelFrontend());
    }
}
