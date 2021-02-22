<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Wishlist\Test\Unit\Block\Customer\Wishlist\Item;

use Magento\Wishlist\Block\Customer\Wishlist\Item\Options;

class OptionsTest extends \PHPUnit\Framework\TestCase
{
    const TEST_PRODUCT_TYPE = 'testProductType';
    const TEST_HELPER_CLASS_NAME = 'testHelperClass';

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit\Framework\MockObject\MockObject
     */
    private $escaperMock;

    /**
     * @var \Magento\Framework\App\Http\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    private $httpContextMock;

    /**
     * @var Options
     */
    private $block;

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool|\PHPUnit\Framework\MockObject\MockObject
     */
    private $helperPoolMock;

    /**
     * @var \Magento\Wishlist\Model\Item|\PHPUnit\Framework\MockObject\MockObject
     */
    private $itemMock;

    protected function setUp(): void
    {
        $productContextMock = $this->getMockBuilder(\Magento\Catalog\Block\Product\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productContextMock->method('getEscaper')
            ->willReturn($this->escaperMock);
        $productContextMock->method('getEventManager')
            ->willReturn($eventManagerMock);

        $this->httpContextMock = $this->getMockBuilder(\Magento\Framework\App\Http\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperPoolMock = $this->getMockBuilder(\Magento\Catalog\Helper\Product\ConfigurationPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemMock = $this->getMockBuilder(\Magento\Wishlist\Model\Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->block = $objectManager->getObject(
            Options::class,
            [
                'context' => $productContextMock,
                'httpContext' => $this->httpContextMock,
                'helperPool' => $this->helperPoolMock,
            ]
        );
        $this->block->setItem($this->itemMock);
        $this->block->addOptionsRenderCfg(self::TEST_PRODUCT_TYPE, self::TEST_HELPER_CLASS_NAME);
    }

    /**
     * @param array $options
     * @param int $callNum
     * @param array $expected
     * @dataProvider getConfiguredOptionsDataProvider
     */
    public function testGetConfiguredOptions($options, $callNum, $expected)
    {
        $productMock = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(self::TEST_PRODUCT_TYPE);
        $this->itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);

        $helperMock = $this->getMockBuilder(\Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $helperMock->expects($this->once())
            ->method('getOptions')
            ->willReturn($options);
        $this->helperPoolMock->expects($this->once())
            ->method('get')
            ->with(self::TEST_HELPER_CLASS_NAME)
            ->willReturn($helperMock);

        $this->escaperMock->expects($this->exactly($callNum))
            ->method('escapeHtml')
            ->willReturnArgument(0);

        $this->assertEquals($expected, $this->block->getConfiguredOptions());
    }

    /**
     * @return array
     */
    public function getConfiguredOptionsDataProvider()
    {
        return [
            [
                [
                    [
                        'label' => 'title',
                        'value' => ['1 x name <span class="price">$15.00</span>'],
                        'has_html' => true,
                    ],
                    ['label' => 'title', 'value' => 'value'],
                    ['label' => 'title', 'value' => ['value']],
                ],
                2,
                [
                    [
                        'label' => 'title',
                        'value' => ['1 x name <span class="price">$15.00</span>'],
                        'has_html' => true,
                    ],
                    ['label' => 'title', 'value' => 'value'],
                    ['label' => 'title', 'value' => ['value']],
                ],
            ]
        ];
    }
}
