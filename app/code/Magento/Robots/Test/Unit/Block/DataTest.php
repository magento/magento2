<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Robots\Test\Unit\Block;

class DataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Robots\Block\Data
     */
    private $block;

    /**
     * @var \Magento\Framework\View\Element\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var \Magento\Robots\Model\Robots|\PHPUnit_Framework_MockObject_MockObject
     */
    private $robots;

    /**
     * @var \Magento\Store\Model\StoreResolver|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeResolver;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    protected function setUp()
    {
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);

        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->robots = $this->getMockBuilder(\Magento\Robots\Model\Robots::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeResolver = $this->getMockBuilder(\Magento\Store\Model\StoreResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManager = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->getMockForAbstractClass();

        $this->block = new \Magento\Robots\Block\Data(
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

        $storeMock = $this->getMockBuilder(\Magento\Store\Api\Data\StoreInterface::class)->getMock();

        $this->storeManager->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $storeMock->expects($this->once())
            ->method('getId')
            ->willReturn($storeId);

        $expected = [
            \Magento\Robots\Model\Config\Value::CACHE_TAG . '_' . $storeId,
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
                        'transport' => new \Magento\Framework\DataObject(['html' => $data]),
                    ],
                ],
            ]);
    }
}
