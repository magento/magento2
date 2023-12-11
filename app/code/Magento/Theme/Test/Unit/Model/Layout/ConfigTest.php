<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Model\Layout;

use Magento\Framework\Config\DataInterface;
use Magento\Framework\DataObject;
use Magento\Theme\Model\Layout\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $_model;

    /**
     * @var DataInterface|MockObject
     */
    protected $dataStorage;

    protected function setUp(): void
    {
        $this->dataStorage = $this->getMockBuilder(DataInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->_model = new Config($this->dataStorage);
    }

    /**
     * @test
     * @return void
     * @covers \Magento\Theme\Model\Layout\Config::getPageLayouts
     * @covers \Magento\Theme\Model\Layout\Config::getPageLayout
     * @covers \Magento\Theme\Model\Layout\Config::getPageLayoutHandles
     * @covers \Magento\Theme\Model\Layout\Config::_initPageLayouts
     * @covers \Magento\Theme\Model\Layout\Config::__construct
     */
    public function testGetPageLayout()
    {
        $data = ['code' => ['label' => 'Test Label', 'code' => 'testCode']];
        $expectedResult = [
            'code' => new DataObject(['label' => __('Test Label'), 'code' => 'testCode']),
        ];

        $this->dataStorage->expects($this->once())
            ->method('get')
            ->with(null, null)
            ->willReturn($data);

        $this->assertEquals($expectedResult, $this->_model->getPageLayouts());
        $this->assertEquals($expectedResult['code'], $this->_model->getPageLayout('code'));
        $this->assertFalse($this->_model->getPageLayout('wrong_code'));
        $this->assertEquals(
            [$expectedResult['code']['code'] => $expectedResult['code']['code']],
            $this->_model->getPageLayoutHandles()
        );
    }
}
