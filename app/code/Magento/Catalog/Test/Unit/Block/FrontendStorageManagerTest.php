<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->frontendStorageConfigurationPoolMock = $this
            ->getMockBuilder(FrontendStorageConfigurationPool::class)
            ->disableOriginalConstructor()
            ->getMock();

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
        $dynamicStorage = $this->getMockBuilder(FrontendStorageConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $configuration = [
            'first_key' => [
                'first' => 'data_before',
            ],
            'second_key' => []
        ];
        $this->model->setData('configuration', $configuration);
        $this->frontendStorageConfigurationPoolMock->expects($this->exactly(2))
            ->method('get')
            ->withConsecutive(['first_key'], ['second_key'])
            ->willReturnOnConsecutiveCalls($dynamicStorage, null);
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
