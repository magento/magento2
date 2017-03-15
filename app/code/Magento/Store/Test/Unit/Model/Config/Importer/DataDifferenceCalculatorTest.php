<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config\Importer;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Store\Model\Config\Importer\DataDifferenceCalculator;
use Magento\Store\Model\ScopeInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;

/**
 * @inheritdoc
 */
class DataDifferenceCalculatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataDifferenceCalculator
     */
    private $model;

    /**
     * @var ConfigSourceInterface|Mock
     */
    private $runtimeConfigSourceMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->runtimeConfigSourceMock = $this->getMockBuilder(ConfigSourceInterface::class)
            ->getMockForAbstractClass();

        $this->model = new DataDifferenceCalculator(
            $this->runtimeConfigSourceMock
        );
    }

    public function testGetItemsToDelete()
    {
        $expectedData = [];
        $data = [
            'test' => [
                'code' => 'test',
                'name' => 'Test',
            ]
        ];

        $this->runtimeConfigSourceMock->expects($this->once())
            ->method('get')
            ->with(ScopeInterface::SCOPE_GROUPS)
            ->willReturn([
                2 => [
                    'code' => 'test',
                    'name' => 'Test'
                ]
            ]);

        $this->assertSame(
            $expectedData,
            $this->model->getItemsToDelete(ScopeInterface::SCOPE_GROUPS, $data)
        );
    }

    public function testGetItemsToCreate()
    {
        $expectedData = [
            'test' => [
                'code' => 'test',
                'name' => 'Test'
            ]
        ];
        $data = [
            2 => [
                'code' => 'test',
                'name' => 'Test'
            ]
        ];

        $this->runtimeConfigSourceMock->expects($this->once())
            ->method('get')
            ->with(ScopeInterface::SCOPE_GROUPS)
            ->willReturn([]);

        $this->assertSame(
            $expectedData,
            $this->model->getItemsToCreate(ScopeInterface::SCOPE_GROUPS, $data)
        );
    }

    public function testGetItemsToUpdate()
    {
        $expectedData = [
            'test' => [
                'code' => 'test',
                'name' => 'Test2'
            ]
        ];
        $data = [
            2 => [
                'code' => 'test',
                'name' => 'Test2'
            ]
        ];

        $this->runtimeConfigSourceMock->expects($this->once())
            ->method('get')
            ->with(ScopeInterface::SCOPE_GROUPS)
            ->willReturn([
                2 => [
                    'code' => 'test',
                    'name' => 'Test'
                ]
            ]);

        $this->assertSame(
            $expectedData,
            $this->model->getItemsToUpdate(ScopeInterface::SCOPE_GROUPS, $data)
        );
    }
}
