<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Block\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Element\ElementCreator;
use Magento\Framework\Data\Form as DataForm;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    use MockCreationTrait;

    /** @var  Form */
    protected $model;

    /** @var  Context|MockObject */
    protected $context;

    /** @var  DataForm|MockObject */
    protected $dataForm;

    /** @var  UrlInterface|MockObject */
    protected $urlBuilder;

    /** @var Data */
    protected $jsonHelperMock;

    /** @var  ElementCreator */
    protected $creatorStub;

    /** @var ObjectManagerHelper */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->prepareContext();

        $this->dataForm = $this->createPartialMockWithReflection(
            \Magento\Framework\Data\Form::class,
            ['setParent', 'setBaseUrl', 'addCustomAttribute']
        );

        $this->jsonHelperMock = $this->createMock(Data::class);

        /** @var ObjectManagerInterface|MockObject $objectManagerMock */
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $objectManagerMock->expects($this->exactly(3))
            ->method('get')
            ->willReturn($this->jsonHelperMock);
        ObjectManager::setInstance($objectManagerMock);

        $this->creatorStub = $this->createMock(ElementCreator::class);

        $this->model = new Form(
            $this->context
        );
    }

    protected function prepareContext()
    {
        $this->urlBuilder = $this->createMock(UrlInterface::class);

        $this->context = $this->createMock(Context::class);
        $this->context->expects($this->any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilder);
    }

    public function testSetForm()
    {
        $baseUrl = 'base_url';
        $attributeKey = 'attribute_key';
        $attributeValue = 'attribute_value';

        $this->dataForm->expects($this->once())
            ->method('setParent')
            ->with($this->model)
            ->willReturnSelf();
        $this->dataForm->expects($this->once())
            ->method('setBaseUrl')
            ->with($baseUrl)
            ->willReturnSelf();
        $this->dataForm->expects($this->once())
            ->method('addCustomAttribute')
            ->with($attributeKey, $attributeValue)
            ->willReturnSelf();

        $this->urlBuilder->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);

        $this->model->setData('custom_attributes', [$attributeKey => $attributeValue]);
        $this->assertEquals($this->model, $this->model->setForm($this->dataForm));
    }

    public function testSetFormNoCustomAttributes()
    {
        $baseUrl = 'base_url';

        $this->dataForm->expects($this->once())
            ->method('setParent')
            ->with($this->model)
            ->willReturnSelf();
        $this->dataForm->expects($this->once())
            ->method('setBaseUrl')
            ->with($baseUrl)
            ->willReturnSelf();

        $this->urlBuilder->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn($baseUrl);

        $this->assertEquals($this->model, $this->model->setForm($this->dataForm));
    }
}
