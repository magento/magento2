<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FormTest extends TestCase
{
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
        $this->context = $this->createMock(Context::class);
        $this->registry = $this->createMock(Registry::class);
        $this->formFactory = $this->createMock(FormFactory::class);
        $this->context->method('getUrlBuilder')
            ->willReturn($this->getMockForAbstractClass(UrlInterface::class));
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
            ->willReturn($this->getMockForAbstractClass(UrlInterface::class));
        $this->model->setData('filter_data', new DataObject(['multiselect' => ['5', '6']]));
        $form = $this->getMockBuilder(Form::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getElements'])
            ->getMockForAbstractClass();
        $element = $this->getMockBuilder(AbstractElement::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $element->setId('multiselect');
        $form->method('getElements')->willReturn(new Collection($form));
        $reflection = new ReflectionClass($form);
        $reflectionProp = $reflection->getProperty('_allElements');
        $reflectionProp->setAccessible(true);
        $reflectionProp->setValue($form, new Collection($form));
        $form->addElement($element);
        $this->model->setForm($form);
        $reflection = new ReflectionClass($this->model);
        $reflectionMethod = $reflection->getMethod('_initFormValues');
        $reflectionMethod->setAccessible(true);
        $reflectionMethod->invoke($this->model);
        $this->assertEquals(['5', '6'], $this->model->getForm()->getElement('multiselect')->getValue());
    }
}
