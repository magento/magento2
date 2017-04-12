<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\UrlRewrite\Test\Unit\Block\Catalog\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\UrlRewrite\Block\Edit\Form */
    protected $form;

    /** @var \Magento\Framework\Data\FormFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $formFactory;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $urlRewriteFactory;

    /** @var \Magento\Catalog\Model\ProductFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $productFactory;

    /** @var \Magento\Catalog\Model\CategoryFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $categoryFactory;

    /** @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $layout;

    protected function setUp()
    {
        $this->layout = $this->getMock(\Magento\Framework\View\LayoutInterface::class);
        $this->formFactory = $this->getMock(\Magento\Framework\Data\FormFactory::class, ['create'], [], '', false);
        $this->urlRewriteFactory = $this->getMock(
            \Magento\UrlRewrite\Model\UrlRewriteFactory::class, ['create'], [], '', false
        );
        $this->urlRewriteFactory->expects($this->once())->method('create')
            ->willReturn($this->getMock(\Magento\UrlRewrite\Model\UrlRewrite::class, [], [], '', false));
        $this->categoryFactory = $this->getMock(
            \Magento\Catalog\Model\CategoryFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->productFactory = $this->getMock(\Magento\Catalog\Model\ProductFactory::class, ['create'], [], '', false);

        $this->form = (new ObjectManager($this))->getObject(\Magento\UrlRewrite\Block\Catalog\Edit\Form::class,
            [
                'layout' => $this->layout,
                'productFactory' => $this->productFactory,
                'categoryFactory' => $this->categoryFactory,
                'formFactory' => $this->formFactory,
                'rewriteFactory' => $this->urlRewriteFactory,
                'data' => ['template' => null],
            ]
        );
    }

    public function testAddErrorMessageWhenProductWithoutStores()
    {
        $form = $this->getMock(\Magento\Framework\Data\Form::class, [], [], '', false);
        $form->expects($this->any())->method('getElement')->will($this->returnValue(
            $this->getMockForAbstractClass(\Magento\Framework\Data\Form\Element\AbstractElement::class, [], '', false))
        );
        $this->formFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($form));
        $fieldset = $this->getMock(\Magento\Framework\Data\Form\Element\Fieldset::class, [], [], '', false);
        $form->expects($this->once())
            ->method('addFieldset')
            ->will($this->returnValue($fieldset));
        $storeElement = $this->getMock(
            \Magento\Framework\Data\Form\Element\AbstractElement::class,
            ['setAfterElementHtml', 'setValues'],
            [],
            '',
            false
        );
        $fieldset->expects($this->at(2))
            ->method('addField')
            ->with(
                'store_id',
                'select',
                [
                    'label' => 'Store',
                    'title' => 'Store',
                    'name' => 'store_id',
                    'required' => true,
                    'value' => 0
                ]
            )
            ->willReturn($storeElement);

        $product = $this->getMock(\Magento\Catalog\Model\Product::class, [], [], '', false);
        $product->expects($this->any())->method('getId')->willReturn('product_id');
        $product->expects($this->once())->method('getStoreIds')->willReturn([]);
        $this->productFactory->expects($this->once())->method('create')->willReturn($product);
        $this->categoryFactory->expects($this->once())->method('create')
            ->willReturn($this->getMock(\Magento\Catalog\Model\Category::class, [], [], '', false));

        $storeElement->expects($this->once())->method('setAfterElementHtml');
        $storeElement->expects($this->once())->method('setValues')->with([]);

        $this->layout->expects($this->once())->method('createBlock')
            ->willReturn($this->getMock(\Magento\Framework\Data\Form\Element\Renderer\RendererInterface::class));

        $this->form->toHtml();
    }
}
