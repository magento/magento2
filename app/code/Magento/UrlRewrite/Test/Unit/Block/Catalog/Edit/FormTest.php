<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewrite\Test\Unit\Block\Catalog\Edit;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\LayoutInterface;
use Magento\UrlRewrite\Block\Catalog\Edit\Form as CatalogEditForm;
use Magento\UrlRewrite\Block\Edit\Form as EditFormBlock;
use Magento\UrlRewrite\Model\UrlRewrite;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FormTest extends TestCase
{
    /**
     * @var EditFormBlock
     */
    protected $form;

    /**
     * @var FormFactory|MockObject
     */
    protected $formFactory;

    /**
     * @var MockObject
     */
    protected $urlRewriteFactory;

    /**
     * @var ProductFactory|MockObject
     */
    protected $productFactory;

    /**
     * @var CategoryFactory|MockObject
     */
    protected $categoryFactory;

    /**
     * @var LayoutInterface|MockObject
     */
    protected $layout;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->layout = $this->getMockForAbstractClass(LayoutInterface::class);
        $this->formFactory = $this->createPartialMock(FormFactory::class, ['create']);
        $this->urlRewriteFactory = $this->createPartialMock(
            UrlRewriteFactory::class,
            ['create']
        );
        $this->urlRewriteFactory->expects($this->once())->method('create')
            ->willReturn($this->createMock(UrlRewrite::class));
        $this->categoryFactory = $this->createPartialMock(CategoryFactory::class, ['create']);
        $this->productFactory = $this->createPartialMock(ProductFactory::class, ['create']);

        $this->form = (new ObjectManager($this))->getObject(
            CatalogEditForm::class,
            [
                'layout' => $this->layout,
                'productFactory' => $this->productFactory,
                'categoryFactory' => $this->categoryFactory,
                'formFactory' => $this->formFactory,
                'rewriteFactory' => $this->urlRewriteFactory,
                'data' => ['template' => null]
            ]
        );
    }

    /**
     * @return void
     */
    public function testAddErrorMessageWhenProductWithoutStores(): void
    {
        $form = $this->createMock(Form::class);
        $form->expects($this->any())->method('getElement')->willReturn(
            $this->getMockForAbstractClass(
                AbstractElement::class,
                [],
                '',
                false
            )
        );
        $this->formFactory->expects($this->once())
            ->method('create')
            ->willReturn($form);
        $fieldset = $this->createMock(Fieldset::class);
        $form->expects($this->once())
            ->method('addFieldset')
            ->willReturn($fieldset);
        $storeElement = $this->getMockBuilder(AbstractElement::class)
            ->addMethods(['setAfterElementHtml', 'setValues'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $fieldset
            ->method('addField')
            ->willReturnCallback(function ($arg1, $arg2, $arg3) use ($storeElement) {
                static $callCount = 0;
                $callCount++;
                switch ($callCount) {
                    case 1:
                    case 2:
                        return null;
                    case 3:
                        if ($arg1 == 'store_id' && $arg2 == 'select' && $arg3 == [
                                'label' => 'Store',
                                'title' => 'Store',
                                'name' => 'store_id',
                                'required' => true,
                                'value' => 0
                            ]) {
                            return $storeElement;
                        }
                }
            });

        $product = $this->createMock(Product::class);
        $product->expects($this->any())->method('getId')->willReturn('product_id');
        $product->expects($this->once())->method('getStoreIds')->willReturn([]);
        $this->productFactory->expects($this->once())->method('create')->willReturn($product);
        $this->categoryFactory->expects($this->once())->method('create')
            ->willReturn($this->createMock(Category::class));

        $storeElement->expects($this->once())->method('setAfterElementHtml');
        $storeElement->expects($this->once())->method('setValues')->with([]);

        $this->layout->expects($this->once())->method('createBlock')
            ->willReturn($this->getMockForAbstractClass(RendererInterface::class));

        $this->form->toHtml();
    }
}
