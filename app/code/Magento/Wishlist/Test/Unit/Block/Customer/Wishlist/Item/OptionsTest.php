<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Block\Customer\Wishlist\Item;

use Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Wishlist\Block\Customer\Wishlist\Item\Options;
use Magento\Wishlist\Model\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    const TEST_PRODUCT_TYPE = 'testProductType';
    const TEST_HELPER_CLASS_NAME = 'testHelperClass';

    /**
     * @var Escaper|MockObject
     */
    private $escaperMock;

    /**
     * @var Context|MockObject
     */
    private $httpContextMock;

    /**
     * @var Options
     */
    private $block;

    /**
     * @var ConfigurationPool|MockObject
     */
    private $helperPoolMock;

    /**
     * @var Item|MockObject
     */
    private $itemMock;

    protected function setUp(): void
    {
        $productContextMock = $this->getMockBuilder(\Magento\Catalog\Block\Product\Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->escaperMock = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $eventManagerMock = $this->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $productContextMock->method('getEscaper')
            ->willReturn($this->escaperMock);
        $productContextMock->method('getEventManager')
            ->willReturn($eventManagerMock);

        $this->httpContextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->helperPoolMock = $this->getMockBuilder(ConfigurationPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
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
        $productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $productMock->expects($this->once())
            ->method('getTypeId')
            ->willReturn(self::TEST_PRODUCT_TYPE);
        $this->itemMock->expects($this->once())
            ->method('getProduct')
            ->willReturn($productMock);

        $helperMock = $this->getMockBuilder(ConfigurationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
    public static function getConfiguredOptionsDataProvider()
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
