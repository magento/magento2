<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Robots\Test\Unit\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\View\Element\Context;
use Magento\Robots\Block\Data;
use Magento\Robots\Model\Config\Value;
use Magento\Robots\Model\Robots;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var Data
     */
    private $block;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var Robots|MockObject
     */
    private $robots;

    /**
     * @var StoreResolver|MockObject
     */
    private $storeResolver;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var ManagerInterface|MockObject
     */
    private $eventManagerMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    protected function setUp(): void
    {
        $this->eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);

        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->robots = $this->getMockBuilder(Robots::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeResolver = $this->getMockBuilder(StoreResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->block = new Data(
            $this->context,
            $this->robots,
            $this->storeResolver,
            $this->storeManager
        );
    }

    /**
     * Check that toHtml() method returns specified text data
     */
    public function testToHtml()
    {
        $data = 'test';

        $this->initEventManagerMock($data);

        $this->scopeConfigMock->expects($this->once())->method('getValue')->willReturn(false);

        $this->robots->expects($this->once())
            ->method('getData')
            ->willReturn($data);

        $this->assertEquals($data . PHP_EOL, $this->block->toHtml());
    }

    /**
     * Check that getIdentities() method returns specified cache tag
     */
    public function testGetIdentities()
    {
        $storeId = 1;

        $storeMock = $this->getMockBuilder(StoreInterface::class)
            ->getMock();

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $expected = [
            Value::CACHE_TAG . '_' . $storeId,
        ];
        $this->assertEquals($expected, $this->block->getIdentities());
    }

    /**
     * Initialize mock object of Event Manager
     *
     * @param string $data
     * @return void
     */
    protected function initEventManagerMock($data)
    {
        $this->eventManagerMock->expects($this->any())
            ->method('dispatch')
            ->willReturnMap([
                [
                    'view_block_abstract_to_html_before',
                    [
                        'block' => $this->block,
                    ],
                ],
                [
                    'view_block_abstract_to_html_after',
                    [
                        'block' => $this->block,
                        'transport' => new DataObject(['html' => $data]),
                    ],
                ],
            ]);
    }
}
