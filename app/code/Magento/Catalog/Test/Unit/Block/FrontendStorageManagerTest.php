<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block;

use Magento\Catalog\Block\FrontendStorageManager;
use Magento\Catalog\Model\FrontendStorageConfigurationInterface;
use Magento\Catalog\Model\FrontendStorageConfigurationPool;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FrontendStorageManagerTest extends TestCase
{
    /** @var FrontendStorageManager */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var Context|MockObject */
    protected $contextMock;

    /** @var FrontendStorageConfigurationPool|MockObject */
    protected $frontendStorageConfigurationPoolMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->frontendStorageConfigurationPoolMock = $this->createMock(FrontendStorageConfigurationPool::class);

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            FrontendStorageManager::class,
            [
                'context' => $this->contextMock,
                'storageConfigurationPool' => $this->frontendStorageConfigurationPoolMock
            ]
        );
    }

    public function testGetConfigurationJson()
    {
        $dynamicStorage = $this->createMock(FrontendStorageConfigurationInterface::class);
        $configuration = [
            'first_key' => [
                'first' => 'data_before',
            ],
            'second_key' => []
        ];
        $this->model->setData('configuration', $configuration);
        $this->frontendStorageConfigurationPoolMock->expects($this->exactly(2))
            ->method('get')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['first_key'] => $dynamicStorage,
                ['second_key'] => null
            });
        $dynamicStorage->expects($this->once())
            ->method('get')
            ->willReturn(['second' => 'data']);

        $this->assertEquals(
            [
                'first_key' => [
                    'first' => 'data_before',
                    'second' => 'data',
                    'allowToSendRequest' => null,
                ],
                'second_key' => [
                    'allowToSendRequest' => null,
                ]
            ],
            json_decode($this->model->getConfigurationJson(), true)
        );
    }
}
