<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test front name prefix
     */
    const TEST_FRONT_NAME = 'test_front_name';

    /**
     * @var array
     */
    protected $_disabledCacheTypes = ['type1', 'type2'];

    /**
     * @var \Magento\DesignEditor\Helper\Data
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_context;

    protected function setUp()
    {
        $this->_context = $this->getMock('Magento\Framework\App\Helper\Context', [], [], '', false);
    }

    protected function tearDown()
    {
        unset($this->_model);
        unset($this->_context);
    }

    public function testGetFrontName()
    {
        $this->_model = new \Magento\DesignEditor\Helper\Data($this->_context, self::TEST_FRONT_NAME);
        $this->assertEquals(self::TEST_FRONT_NAME, $this->_model->getFrontName());
    }

    /**
     * @param string $path
     * @param bool $expected
     * @dataProvider isVdeRequestDataProvider
     */
    public function testIsVdeRequest($path, $expected)
    {
        $this->_model = new \Magento\DesignEditor\Helper\Data($this->_context, self::TEST_FRONT_NAME);
        $requestMock = $this->getMock('Magento\Framework\App\Request\Http', [], [], '', false);
        $requestMock->expects($this->once())->method('getOriginalPathInfo')->will($this->returnValue($path));
        $this->assertEquals($expected, $this->_model->isVdeRequest($requestMock));
    }

    /**
     * @return array
     */
    public function isVdeRequestDataProvider()
    {
        $vdePath = self::TEST_FRONT_NAME . '/' . \Magento\DesignEditor\Model\State::MODE_NAVIGATION . '/';
        return [
            [$vdePath . '1/category.html', true],
            ['/1/category.html', false],
            ['/1/2/3/4/5/6/7/category.html', false]
        ];
    }

    public function testGetDisabledCacheTypes()
    {
        $this->_model = new \Magento\DesignEditor\Helper\Data(
            $this->_context,
            self::TEST_FRONT_NAME,
            ['type1', 'type2']
        );
        $this->assertEquals($this->_disabledCacheTypes, $this->_model->getDisabledCacheTypes());
    }
}
