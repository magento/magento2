<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Block\Adminhtml\Edit\Tab\View\Grid\Renderer;

use Magento\Backend\Block\Context;
use Magento\Bundle\Helper\Catalog\Product\Configuration;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Catalog\Model\Product;
use Magento\Customer\Block\Adminhtml\Edit\Tab\View\Grid\Renderer\Item;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemTest extends TestCase
{
    /** @var  MockObject */
    protected $item;

    /** @var  Item */
    protected $itemBlock;

    /**
     * @param $amountOption
     * @param bool $withoutOptions
     */
    public function configure($amountOption, $withoutOptions = false)
    {
        $options = [];
        for ($i = 1; $i <= $amountOption; $i++) {
            $options[] = [
                'label' => "testLabel{$i}",
                'value' => ['1 x Configurable Product 49-option 3 <span class="price">$10.00</span>']
            ];
        }

        $product = $this->createPartialMock(Product::class, ['getTypeId', 'getName']);
        $product
            ->expects($this->once())
            ->method('getTypeId')
            ->willReturn(null);
        $product
            ->expects($this->once())
            ->method('getName')
            ->willReturn('testProductName');

        $this->item = $this->createPartialMock(\Magento\Wishlist\Model\Item::class, ['getProduct']);
        $this->item
            ->expects($this->atLeastOnce())
            ->method('getProduct')
            ->willReturn($product);

        $productConfigPool = $this->createPartialMock(
            ConfigurationPool::class,
            ['get']
        );
        $helper = $this->createPartialMock(Configuration::class, ['getOptions']);
        $objectManager = new ObjectManager($this);
        $productConfig = $objectManager->getObject(\Magento\Catalog\Helper\Product\Configuration::class);
        $escaper = $objectManager->getObject(Escaper::class);
        if ($withoutOptions) {
            $helper
                ->expects($this->once())
                ->method('getOptions')
                ->willReturn(null);
        } else {
            $helper
                ->expects($this->once())
                ->method('getOptions')
                ->willReturn($options);
        }

        $context = $this->createPartialMock(Context::class, ['getEscaper']);
        $context
            ->expects($this->once())
            ->method('getEscaper')
            ->willReturn($escaper);

        $productConfigPool
            ->expects($this->once())
            ->method('get')
            ->with(\Magento\Catalog\Helper\Product\Configuration::class)
            ->willReturn($helper);

        $this->itemBlock = new Item(
            $context,
            $productConfig,
            $productConfigPool
        );
    }

    public function testRenderWithoutOptions()
    {
        $this->configure(0, true);
        $this->itemBlock->render($this->item);
    }

    /**
     * @dataProvider optionHtmlProvider
     */
    public function testRender($amountOption, $expectedHtml)
    {
        $this->configure($amountOption);
        $realHtml = '<xhtml>' . $this->itemBlock->render($this->item) . '</xhtml>';
        $this->assertXmlStringEqualsXmlString($expectedHtml, $realHtml);
    }

    /**
     * @return array
     */
    public function optionHtmlProvider()
    {
        //phpcs:disable
        return [
            [
                2,
                <<<HTML
                        <xhtml>
                            <div class="product-title">testProductName</div>
                            <dl class="item-options">
                                <dt>testLabel1</dt>
                                <dd>1 x Configurable Product 49-option 3 &lt;span class="price"&gt;$10.00&lt;/span&gt;</dd>
                                <dt>testLabel2</dt>
                                <dd>1 x Configurable Product 49-option 3 &lt;span class="price"&gt;$10.00&lt;/span&gt;</dd>
                            </dl>
                        </xhtml>
HTML
            ],
            [
                1,
                <<<HTML
                        <xhtml>
                            <div class="product-title">testProductName</div>
                            <dl class="item-options">
                                <dt>testLabel1</dt>
                                <dd>1 x Configurable Product 49-option 3 &lt;span class="price"&gt;$10.00&lt;/span&gt;</dd>
                            </dl>
                        </xhtml>
HTML
            ],
        ];//phpcs:enable
    }
}
