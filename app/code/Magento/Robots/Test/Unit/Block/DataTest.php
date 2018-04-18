<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Robots\Test\Unit\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;

class DataTest extends \PHPUnit_Framework_TestCase
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
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfigMock;

    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventManagerMock;

    protected function setUp()
    {
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->context = $this->getMockBuilder(\Magento\Framework\View\Element\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->any())
            ->method('getEventManager')
            ->willReturn($this->eventManagerMock);

        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMockForAbstractClass();
        $this->context->expects($this->any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->robots = $this->getMockBuilder(\Magento\Robots\Model\Robots::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeResolver = $this->getMockBuilder(\Magento\Store\Model\StoreResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->block = new \Magento\Robots\Block\Data(
            $this->context,
            $this->robots,
            $this->storeResolver
        );
    }

    /**
     * Check that toHtml() method returns specified text data
     */
    public function testToHtml()
    {
        $data = 'test';

        $this->initEventManagerMock($data);

        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(
                'advanced/modules_disable_output/Magento_Robots',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                null
            )
            ->willReturn(false);

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

        $this->storeResolver->expects($this->once())
            ->method('getCurrentStoreId')
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
