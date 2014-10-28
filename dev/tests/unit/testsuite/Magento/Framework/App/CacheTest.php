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
namespace Magento\Framework\App;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cache
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    protected $_cacheTypeMocks;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheFrontendMock;

    protected function setUp()
    {
        $this->_initCacheTypeMocks();

        $this->_cacheFrontendMock = $this->getMockForAbstractClass(
            'Magento\Framework\Cache\FrontendInterface',
            array(),
            '',
            true,
            true,
            true,
            array('clean')
        );

        $frontendPoolMock = $this->getMock('Magento\Framework\App\Cache\Frontend\Pool', array(), array(), '', false);
        $frontendPoolMock->expects($this->any())->method('valid')->will($this->onConsecutiveCalls(true, false));

        $frontendPoolMock->expects(
            $this->any()
        )->method(
            'current'
        )->will(
            $this->returnValue($this->_cacheFrontendMock)
        );
        $frontendPoolMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            \Magento\Framework\App\Cache\Frontend\Pool::DEFAULT_FRONTEND_ID
        )->will(
            $this->returnValue($this->_cacheFrontendMock)
        );

        $this->_model = new \Magento\Framework\App\Cache($frontendPoolMock);
    }

    /**
     * Init necessary cache type mocks
     */
    protected function _initCacheTypeMocks()
    {
        $cacheTypes = array(
            'Magento\Framework\Cache\Frontend\Decorator\TagScope',
            'Magento\Framework\Cache\Frontend\Decorator\Bare'
        );
        foreach ($cacheTypes as $type) {
            $this->_cacheTypeMocks[$type] = $this->getMock(
                $type,
                array('clean'),
                array($this->getMockForAbstractClass('Magento\Framework\Cache\FrontendInterface'), 'FIXTURE_TAG')
            );
        }
    }

    /**
     * Callback for the object manager to get different cache type mocks
     *
     * @param string $type Class of the cache type
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getTypeMock($type)
    {
        return $this->_cacheTypeMocks[$type];
    }

    protected function tearDown()
    {
        $this->_cacheTypeMocks = array();
        $this->_cacheFrontendMock = null;
        $this->_model = null;
    }

    public function testConstructor()
    {
        $this->assertSame($this->_cacheFrontendMock, $this->_model->getFrontend());
    }

    public function testGetFrontend()
    {
        $frontend = $this->_model->getFrontend();
        $this->assertSame($this->_cacheFrontendMock, $frontend);
    }

    public function testLoad()
    {
        $this->_cacheFrontendMock->expects(
            $this->once()
        )->method(
            'load'
        )->with(
            'test_id'
        )->will(
            $this->returnValue('test_data')
        );
        $this->assertEquals('test_data', $this->_model->load('test_id'));
    }

    /**
     * @dataProvider saveDataProvider
     * @param string|mixed $inputData
     * @param string $inputId
     * @param array $inputTags
     * @param string $expectedData
     * @param string $expectedId
     * @param array $expectedTags
     */
    public function testSave($inputData, $inputId, $inputTags, $expectedData, $expectedId, $expectedTags)
    {
        $this->_cacheFrontendMock->expects(
            $this->once()
        )->method(
            'save'
        )->with(
            $this->identicalTo($expectedData),
            $expectedId,
            $expectedTags
        );
        $this->_model->save($inputData, $inputId, $inputTags);
    }

    public function saveDataProvider()
    {
        $configTag = \Magento\Framework\App\Config::CACHE_TAG;
        return array(
            'default tags' => array('test_data', 'test_id', array(), 'test_data', 'test_id', array()),
            'config tags' => array(
                'test_data',
                'test_id',
                array($configTag),
                'test_data',
                'test_id',
                array($configTag)
            ),
            'lowercase tags' => array(
                'test_data',
                'test_id',
                array('test_tag'),
                'test_data',
                'test_id',
                array('test_tag')
            ),
            'non-string data' => array(1234567890, 'test_id', array(), '1234567890', 'test_id', array())
        );
    }

    /**
     * @dataProvider successFailureDataProvider
     * @param bool $result
     */
    public function testRemove($result)
    {
        $this->_cacheFrontendMock->expects(
            $this->once()
        )->method(
            'remove'
        )->with(
            'test_id'
        )->will(
            $this->returnValue($result)
        );
        $this->assertEquals($result, $this->_model->remove('test_id'));
    }

    public function successFailureDataProvider()
    {
        return array('success' => array(true), 'failure' => array(false));
    }

    public function testCleanByTags()
    {
        $expectedTags = array('test_tag');
        $this->_cacheFrontendMock->expects(
            $this->once()
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
            $expectedTags
        )->will(
            $this->returnValue(true)
        );
        $this->assertTrue($this->_model->clean($expectedTags));
    }

    public function testCleanByEmptyTags()
    {
        $this->_cacheFrontendMock->expects(
            $this->once()
        )->method(
            'clean'
        )->with(
            \Zend_Cache::CLEANING_MODE_ALL
        )->will(
            $this->returnValue(true)
        );
        $this->assertTrue($this->_model->clean());
    }
}
