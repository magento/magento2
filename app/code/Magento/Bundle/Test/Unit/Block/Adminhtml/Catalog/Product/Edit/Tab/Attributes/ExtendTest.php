<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes;

use Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Extend;
use Magento\Catalog\Model\Product;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Constraint\ArrayHasKey;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ExtendTest extends TestCase
{
    /** @var Registry|MockObject */
    protected $registry;

    /** @var FormFactory|MockObject */
    protected $formFactory;

    /** @var ObjectManager */
    protected $objectManagerHelper;

    /** @var Extend */
    protected $object;

    protected function setUp(): void
    {
        $this->registry = $this->createMock(Registry::class);
        $this->formFactory = $this->createMock(
            FormFactory::class
        );
        $this->objectManagerHelper = new ObjectManager($this);
        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $this->objectManagerHelper->prepareObjectManager($objects);
        $this->object = $this->objectManagerHelper->getObject(
            Extend::class,
            ['registry' => $this->registry, 'formFactory' => $this->formFactory]
        );
    }

    /**
     * @return MockObject
     */
    public function getProduct()
    {
        $product = $this->createMock(Product::class);
        $this->registry->expects($this->once())
            ->method('registry')
            ->with('product')
            ->willReturn(
                $product
            );
        return $product;
    }

    public function testGetExtendedElement()
    {
        $switchAttributeCode = 'test_code';
        $form = $this->createMock(Form::class);
        $hasKey = new ArrayHasKey('value');
        $form->expects($this->once())->method('addField')->with(
            $switchAttributeCode,
            'select',
            $hasKey
        );

        $this->formFactory->expects($this->once())->method('create')->with()->willReturn($form);
        $product = $this->getProduct();
        $product->expects($this->once())->method('getData')->with($switchAttributeCode)->willReturn(
            123
        );
        $this->object->setIsDisabledField(true);
        $this->object->getExtendedElement($switchAttributeCode);
    }
}
