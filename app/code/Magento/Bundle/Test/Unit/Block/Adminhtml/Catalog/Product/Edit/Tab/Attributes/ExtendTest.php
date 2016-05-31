<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes;

use Magento\Catalog\Model\Product;

class ExtendTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Registry|\PHPUnit_Framework_MockObject_MockObject */
    protected $registry;

    /** @var \Magento\Framework\Data\FormFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $formFactory;

    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    protected $objectManagerHelper;

    /** @var \Magento\Bundle\Block\Adminhtml\Catalog\Product\Edit\Tab\Attributes\Extend */
    protected $object;

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder('Magento\\Framework\\Registry')->disableOriginalConstructor()->getMock(
        );
        $this->formFactory = $this->getMockBuilder('Magento\\Framework\\Data\\FormFactory')->disableOriginalConstructor(
        )->getMock();
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->object = $this->objectManagerHelper->getObject(
            'Magento\\Bundle\\Block\\Adminhtml\\Catalog\\Product\\Edit\\Tab\\Attributes\\Extend',
            ['registry' => $this->registry, 'formFactory' => $this->formFactory]
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    public function getProduct()
    {
        $product = $this->getMockBuilder(Product::class)->disableOriginalConstructor()->getMock();
        $this->registry->expects($this->once())
            ->method('registry')
            ->with('product')
            ->will(
                $this->returnValue($product)
            );
        return $product;
    }

    public function testGetExtendedElement()
    {
        $switchAttributeCode = 'test_code';
        $form = $this->getMockBuilder(\Magento\Framework\Data\Form::class)->disableOriginalConstructor()->getMock();
        $and = new \PHPUnit_Framework_Constraint_And();
        $and->setConstraints(
            [
                new \PHPUnit_Framework_Constraint_ArrayHasKey('value')
            ]
        );
        $form->expects($this->once())->method('addField')->with(
            $switchAttributeCode,
            'select',
            $and
        );

        $this->formFactory->expects($this->once())->method('create')->with()->will($this->returnValue($form));
        $product = $this->getProduct();
        $product->expects($this->once())->method('getData')->with($switchAttributeCode)->will(
            $this->returnValue(123)
        );
        $this->object->setIsDisabledField(true);
        $this->object->getExtendedElement($switchAttributeCode);
    }
}
