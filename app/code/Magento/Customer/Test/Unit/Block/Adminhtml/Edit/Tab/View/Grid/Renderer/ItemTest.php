<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Block\Adminhtml\Edit\Tab\View\Grid\Renderer;

class ItemTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $item;

    /** @var  \Magento\Customer\Block\Adminhtml\Edit\Tab\View\Grid\Renderer\Item */
    protected $itemBlock;

    public function configure($amountOption, $withoutOptions = false)
    {
        $options = [];
        for ($i = 1; $i <= $amountOption; $i++) {
            $options[] = [
                'label' => "testLabel{$i}",
                'value' => ['1 x Configurable Product 49-option 3 <span class="price">$10.00</span>']
            ];
        }

        $product = $this->getMock('Magento\Catalog\Model\Product', ['getTypeId', 'getName'], [], '', false);
        $product
            ->expects($this->once())
            ->method('getTypeId')
            ->will($this->returnValue(null));
        $product
            ->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('testProductName'));

        $this->item = $this->getMock('Magento\Wishlist\Model\Item', ['getProduct'], [], '', false);
        $this->item
            ->expects($this->atLeastOnce())
            ->method('getProduct')
            ->will($this->returnValue($product));

        $productConfig = $this->getMock('Magento\Catalog\Helper\Product\Configuration', null, [], '', false);
        $productConfigPool = $this->getMock('Magento\Catalog\Helper\Product\ConfigurationPool', ['get'], [], '', false);

        $helper = $this->getMock('Magento\Bundle\Helper\Catalog\Product\Configuration', ['getOptions'], [], '', false);
        $escaper = $this->getMock('Magento\Framework\Escaper', ['escapeHtml'], [], '', false);
        if ($withoutOptions) {
            $helper
                ->expects($this->once())
                ->method('getOptions')
                ->will($this->returnValue(null));
            $escaper
                ->expects($this->once())
                ->method('escapeHtml')
                ->with('testProductName')
                ->will($this->returnValue('testProductName'));
        } else {
            $helper
                ->expects($this->once())
                ->method('getOptions')
                ->will($this->returnValue($options));

            $escaper
                ->expects($this->at(0))
                ->method('escapeHtml')
                ->with('testProductName')
                ->will($this->returnValue('testProductName'));
            for ($i = 1; $i <= count($options); $i++) {
                $escaper
                    ->expects($this->at($i))
                    ->method('escapeHtml')
                    ->will($this->returnValue("testLabel{$i}"));
            }
        }

        $context = $this->getMock('Magento\Backend\Block\Context', ['getEscaper'], [], '', false);
        $context
            ->expects($this->once())
            ->method('getEscaper')
            ->will($this->returnValue($escaper));

        $productConfigPool
            ->expects($this->once())
            ->method('get')
            ->with('Magento\Catalog\Helper\Product\Configuration')
            ->will($this->returnValue($helper));

        $this->itemBlock = new \Magento\Customer\Block\Adminhtml\Edit\Tab\View\Grid\Renderer\Item(
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

    public function optionHtmlProvider()
    {
        return [
            [
                2,
                <<<HTML
                        <xhtml>
                            <div class="product-title">testProductName</div>
                            <dl class="item-options">
                                <dt>testLabel1</dt>
                                <dd>1 x Configurable Product 49-option 3 <span class="price">$10.00</span></dd>
                                <dt>testLabel2</dt>
                                <dd>1 x Configurable Product 49-option 3 <span class="price">$10.00</span></dd>
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
                                <dd>1 x Configurable Product 49-option 3 <span class="price">$10.00</span></dd>
                            </dl>
                        </xhtml>
HTML
            ],
        ];
    }
}
