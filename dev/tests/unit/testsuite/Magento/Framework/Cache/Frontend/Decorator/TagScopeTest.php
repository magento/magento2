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

class TagScopeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Cache\Frontend\Decorator\TagScope
     */
    protected $_object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_frontend;

    protected function setUp()
    {
        $this->_frontend = $this->getMock('Magento\Framework\Cache\FrontendInterface');
        $this->_object = new \Magento\Framework\Cache\Frontend\Decorator\TagScope($this->_frontend, 'enforced_tag');
    }

    protected function tearDown()
    {
        $this->_object = null;
        $this->_frontend = null;
    }

    public function testGetTag()
    {
        $this->assertEquals('enforced_tag', $this->_object->getTag());
    }

    public function testSave()
    {
        $expectedResult = new \stdClass();
        $this->_frontend->expects(
            $this->once()
        )->method(
            'save'
        )->with(
            'test_value',
            'test_id',
            array('test_tag_one', 'test_tag_two', 'enforced_tag'),
            111
        )->will(
            $this->returnValue($expectedResult)
        );
        $actualResult = $this->_object->save('test_value', 'test_id', array('test_tag_one', 'test_tag_two'), 111);
        $this->assertSame($expectedResult, $actualResult);
    }

    public function testCleanModeAll()
    {
        $expectedResult = new \stdClass();
        $this->_frontend->expects(
            $this->once()
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array('enforced_tag')
        )->will(
            $this->returnValue($expectedResult)
        );
        $actualResult = $this->_object->clean(
            \Zend_Cache::CLEANING_MODE_ALL,
            array('ignored_tag_one', 'ignored_tag_two')
        );
        $this->assertSame($expectedResult, $actualResult);
    }

    public function testCleanModeMatchingTag()
    {
        $expectedResult = new \stdClass();
        $this->_frontend->expects(
            $this->once()
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array('test_tag_one', 'test_tag_two', 'enforced_tag')
        )->will(
            $this->returnValue($expectedResult)
        );
        $actualResult = $this->_object->clean(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array('test_tag_one', 'test_tag_two')
        );
        $this->assertSame($expectedResult, $actualResult);
    }

    /**
     * @param bool $fixtureResultOne
     * @param bool $fixtureResultTwo
     * @param bool $expectedResult
     * @dataProvider cleanModeMatchingAnyTagDataProvider
     */
    public function testCleanModeMatchingAnyTag($fixtureResultOne, $fixtureResultTwo, $expectedResult)
    {
        $this->_frontend->expects(
            $this->at(0)
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array('test_tag_one', 'enforced_tag')
        )->will(
            $this->returnValue($fixtureResultOne)
        );
        $this->_frontend->expects(
            $this->at(1)
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_TAG,
            array('test_tag_two', 'enforced_tag')
        )->will(
            $this->returnValue($fixtureResultTwo)
        );
        $actualResult = $this->_object->clean(
            \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
            array('test_tag_one', 'test_tag_two')
        );
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function cleanModeMatchingAnyTagDataProvider()
    {
        return array(
            'failure, failure' => array(false, false, false),
            'failure, success' => array(false, true, true),
            'success, failure' => array(true, false, true),
            'success, success' => array(true, true, true)
        );
    }
}
