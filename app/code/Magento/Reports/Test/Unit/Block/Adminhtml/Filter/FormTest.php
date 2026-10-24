<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Block\Adminhtml\Filter;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Collection;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FormTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var Registry|MockObject
     */
    private $registry;

    /**
     * @var FormFactory|MockObject
     */
    private $formFactory;

    /**
     * @var \Magento\Reports\Block\Adminhtml\Filter\Form
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = new ObjectManager($this);
        $objectManager->prepareObjectManager();
        
        $this->context = $this->createMock(Context::class);
        $this->registry = $this->createMock(Registry::class);
        $this->formFactory = $this->createMock(FormFactory::class);
        $this->context->method('getUrlBuilder')
            ->willReturn($this->createMock(UrlInterface::class));
        $this->model = new \Magento\Reports\Block\Adminhtml\Filter\Form(
            $this->context,
            $this->registry,
            $this->formFactory
        );
    }

    /**
     * @return void
     * @throws \ReflectionException
     */
    public function testMultiselectInitialValues(): void
    {
        $this->context->method('getUrlBuilder')
            ->willReturn($this->createMock(UrlInterface::class));
        $this->model->setData('filter_data', new DataObject(['multiselect' => ['5', '6']]));
        $form = $this->createPartialMock(Form::class, ['getElements', 'getElement']);
        $element = $this->createPartialMockWithReflection(
            AbstractElement::class,
            ['setValue', 'getValue', 'setId', 'getId']
        );

        // Simulate element behavior: capture value when setValue is called
        $capturedValue = null;
        $element->method('setValue')->willReturnCallback(function ($value) use (&$capturedValue, $element) {
            $capturedValue = $value;
            return $element;
        });
        $element->method('getValue')->willReturnCallback(function () use (&$capturedValue) {
            return $capturedValue;
        });
        $element->method('getId')->willReturn('multiselect');
        
        $element->setId('multiselect');
        $form->method('getElements')->willReturn(new Collection($form));
        $form->method('getElement')->with('multiselect')->willReturn($element);
        $reflection = new ReflectionClass($form);
        $reflectionProp = $reflection->getProperty('_allElements');
        $reflectionProp->setValue($form, new Collection($form));
        $form->addElement($element);
        $this->model->setForm($form);
        $reflection = new ReflectionClass($this->model);
        $reflectionMethod = $reflection->getMethod('_initFormValues');
        $reflectionMethod->invoke($this->model);
        $this->assertEquals(['5', '6'], $this->model->getForm()->getElement('multiselect')->getValue());
    }
}
