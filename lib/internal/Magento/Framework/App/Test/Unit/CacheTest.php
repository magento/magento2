<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Test\Unit;

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
            \Magento\Framework\Cache\FrontendInterface::class,
            [],
            '',
            true,
            true,
            true,
            ['clean']
        );

        $frontendPoolMock = $this->getMock(\Magento\Framework\App\Cache\Frontend\Pool::class, [], [], '', false);
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
        $cacheTypes = [
            \Magento\Framework\Cache\Frontend\Decorator\TagScope::class,
            \Magento\Framework\Cache\Frontend\Decorator\Bare::class,
        ];
        foreach ($cacheTypes as $type) {
            $this->_cacheTypeMocks[$type] = $this->getMock(
                $type,
                ['clean'],
                [$this->getMockForAbstractClass(\Magento\Framework\Cache\FrontendInterface::class), 'FIXTURE_TAG']
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
        $this->_cacheTypeMocks = [];
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
        return [
            'default tags' => ['test_data', 'test_id', [], 'test_data', 'test_id', []],
            'config tags' => [
                'test_data',
                'test_id',
                [$configTag],
                'test_data',
                'test_id',
                [$configTag],
            ],
            'lowercase tags' => [
                'test_data',
                'test_id',
                ['test_tag'],
                'test_data',
                'test_id',
                ['test_tag'],
            ],
            'non-string data' => [1234567890, 'test_id', [], '1234567890', 'test_id', []]
        ];
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
        return ['success' => [true], 'failure' => [false]];
    }

    public function testCleanByTags()
    {
        $expectedTags = ['test_tag'];
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
