<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Unit\Block\Catalog\Edit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends \PHPUnit\Framework\TestCase
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
        $this->layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $this->formFactory = $this->createPartialMock(\Magento\Framework\Data\FormFactory::class, ['create']);
        $this->urlRewriteFactory = $this->createPartialMock(
            \Magento\UrlRewrite\Model\UrlRewriteFactory::class,
            ['create']
        );
        $this->urlRewriteFactory->expects($this->once())->method('create')
            ->willReturn($this->createMock(\Magento\UrlRewrite\Model\UrlRewrite::class));
        $this->categoryFactory = $this->createPartialMock(\Magento\Catalog\Model\CategoryFactory::class, ['create']);
        $this->productFactory = $this->createPartialMock(\Magento\Catalog\Model\ProductFactory::class, ['create']);

        $this->form = (new ObjectManager($this))->getObject(
            \Magento\UrlRewrite\Block\Catalog\Edit\Form::class,
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
        $form = $this->createMock(\Magento\Framework\Data\Form::class);
        $form->expects($this->any())->method('getElement')->will(
            $this->returnValue(
                $this->getMockForAbstractClass(
                    \Magento\Framework\Data\Form\Element\AbstractElement::class,
                    [],
                    '',
                    false
                )
            )
        );
        $this->formFactory->expects($this->once())
            ->method('create')
            ->will($this->returnValue($form));
        $fieldset = $this->createMock(\Magento\Framework\Data\Form\Element\Fieldset::class);
        $form->expects($this->once())
            ->method('addFieldset')
            ->will($this->returnValue($fieldset));
        $storeElement = $this->createPartialMock(
            \Magento\Framework\Data\Form\Element\AbstractElement::class,
            ['setAfterElementHtml', 'setValues']
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

        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->any())->method('getId')->willReturn('product_id');
        $product->expects($this->once())->method('getStoreIds')->willReturn([]);
        $this->productFactory->expects($this->once())->method('create')->willReturn($product);
        $this->categoryFactory->expects($this->once())->method('create')
            ->willReturn($this->createMock(\Magento\Catalog\Model\Category::class));

        $storeElement->expects($this->once())->method('setAfterElementHtml');
        $storeElement->expects($this->once())->method('setValues')->with([]);

        $this->layout->expects($this->once())->method('createBlock')
            ->willReturn($this->createMock(\Magento\Framework\Data\Form\Element\Renderer\RendererInterface::class));

        $this->form->toHtml();
    }
}
