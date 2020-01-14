<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Test\Unit;

use \Magento\Framework\Data\Form;

/**
 * Tests for \Magento\Framework\Data\FormFactory
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class FormTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryElementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formKeyMock;

    /**
     * @var \Magento\Framework\Data\Form
     */
    protected $_form;

    protected function setUp()
    {
        $this->_factoryElementMock = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);

        $this->_factoryCollectionMock =
            $this->createPartialMock(\Magento\Framework\Data\Form\Element\CollectionFactory::class, ['create']);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $collectionModel = $objectManager->getObject(\Magento\Framework\Data\Form\Element\Collection::class);

        $this->_factoryCollectionMock
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($collectionModel));

        $this->_formKeyMock = $this->createPartialMock(\Magento\Framework\Data\Form\FormKey::class, ['getFormKey']);

        $this->_form = new Form($this->_factoryElementMock, $this->_factoryCollectionMock, $this->_formKeyMock);
    }

    public function testFormKeyUsing()
    {
        $formKey = 'form-key';
        $this->_formKeyMock->expects($this->once())->method('getFormKey')->will($this->returnValue($formKey));

        $this->_form->setUseContainer(true);
        $this->_form->setMethod('post');
        $this->assertContains($formKey, $this->_form->toHtml());
    }

    public function testSettersGetters()
    {
        $setElementRenderer = $this->getMockBuilder(\Magento\Backend\Block\Widget\Form\Renderer\Element::class)
            ->disableOriginalConstructor()
            ->getMock();

        // note: this results in setting a static variable in the Form class
        $this->_form->setElementRenderer($setElementRenderer);
        $getElementRenderer = $this->_form->getElementRenderer();
        $this->assertSame($setElementRenderer, $getElementRenderer);
        // restore our Form to its earlier state
        $this->_form->setElementRenderer(null);

        $setFieldsetRenderer = $this->getMockBuilder(\Magento\Backend\Block\Widget\Form\Renderer\Fieldset::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_form->setFieldsetRenderer($setFieldsetRenderer);
        $getFieldsetRenderer = $this->_form->getFieldsetRenderer();
        $this->assertSame($setFieldsetRenderer, $getFieldsetRenderer);

        $setFieldsetElementRenderer = $this->getMockBuilder(\Magento\Backend\Block\Widget\Form\Renderer\Fieldset::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_form->setFieldsetElementRenderer($setFieldsetElementRenderer);
        $getFieldsetElementRenderer = $this->_form->getFieldsetElementRenderer();
        $this->assertSame($setFieldsetElementRenderer, $getFieldsetElementRenderer);

        $this->assertSame($this->_form->getHtmlAttributes(), ['id', 'name', 'method',
            'action', 'enctype', 'class', 'onsubmit', 'target']);

        $this->_form->setFieldContainerIdPrefix('abc');
        $this->assertSame($this->_form->getFieldContainerIdPrefix(), 'abc');

        $result = $this->_form->addSuffixToName('123', 'abc');
        $this->assertSame($result, 'abc[123]');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage An element with a "1" ID already exists.
     */
    public function testElementExistsException()
    {
        $buttonElement = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\Button::class)
            ->disableOriginalConstructor()
            ->getMock();
        $buttonElement->expects($this->any())->method('getId')->will($this->returnValue('1'));

        $this->_form->addElement($buttonElement);
        $this->_form->addElementToCollection($buttonElement);

        $this->_form->checkElementId($buttonElement->getId());
    }

    public function testElementOperations()
    {
        $buttonElement = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\Button::class)
            ->disableOriginalConstructor()
            ->getMock();
        $buttonElement->expects($this->any())->method('getId')->will($this->returnValue('1'));
        $buttonElement->expects($this->any())->method('getName')->will($this->returnValue('Hero'));

        $this->_form->addElement($buttonElement);
        $this->_form->addElementToCollection($buttonElement);

        $this->_form->addValues(['1', '2', '3']);
        $this->_form->setValues(['4', '5', '6']);

        $this->_form->addFieldNameSuffix('abc123');

        $this->_form->removeField($buttonElement->getId());
        $this->assertSame($this->_form->checkElementId($buttonElement->getId()), true);
    }
}
