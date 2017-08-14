<?php
/**
 * Copyright © Magento, Inc. All rights reserved. 
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Block;

use Magento\Catalog\Model\FrontendStorageConfigurationInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class FrontendStorageManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Block\FrontendStorageManager */
    protected $model;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;

    /** @var \Magento\Catalog\Model\FrontendStorageConfigurationPool|\PHPUnit_Framework_MockObject_MockObject */
    protected $frontendStorageConfigurationPoolMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->frontendStorageConfigurationPoolMock = $this
            ->getMockBuilder(\Magento\Catalog\Model\FrontendStorageConfigurationPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->model = $this->objectManagerHelper->getObject(
            \Magento\Catalog\Block\FrontendStorageManager::class,
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
