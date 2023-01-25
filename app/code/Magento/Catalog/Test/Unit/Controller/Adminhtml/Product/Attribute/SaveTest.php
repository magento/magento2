<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Attribute;

use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation;
use Magento\Catalog\Model\Product\AttributeSet\Build;
use Magento\Catalog\Model\Product\AttributeSet\BuildFactory;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\AttributeTest;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator as InputTypeValidator;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Eav\Model\Validator\Attribute\Code as AttributeCodeValidator;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Serialize\Serializer\FormData;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class SaveTest extends AttributeTest
{
    /**
     * @var BuildFactory|MockObject
     */
    private $buildFactoryMock;

    /**
     * @var FilterManager|MockObject
     */
    private $filterManagerMock;

    /**
     * @var ProductHelper|MockObject
     */
    private $productHelperMock;

    /**
     * @var AttributeFactory|MockObject
     */
    private $attributeFactoryMock;

    /**
     * @var ValidatorFactory|MockObject
     */
    private $validatorFactoryMock;

    /**
     * @var CollectionFactory|MockObject
     */
    private $groupCollectionFactoryMock;

    /**
     * @var LayoutFactory|MockObject
     */
    private $layoutFactoryMock;

    /**
     * @var ResultRedirect|MockObject
     */
    private $redirectMock;

    /**
     * @var AttributeSetInterface|MockObject
     */
    private $attributeSetMock;

    /**
     * @var Build|MockObject
     */
    private $builderMock;

    /**
     * @var InputTypeValidator|MockObject
     */
    private $inputTypeValidatorMock;

    /**
     * @var FormData|MockObject
     */
    private $formDataSerializerMock;

    /**
     * @var ProductAttributeInterface|MockObject
     */
    private $productAttributeMock;

    /**
     * @var AttributeCodeValidator|MockObject
     */
    private $attributeCodeValidatorMock;

    /**
     * @var Presentation|MockObject
     */
    private $presentationMock;

    /**
     * @var Session|MockObject
     */

    private $sessionMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filterManagerMock = $this->getMockBuilder(FilterManager::class)
            ->addMethods(['stripTags'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productHelperMock = $this->createMock(ProductHelper::class);
        $this->attributeSetMock = $this->createMock(AttributeSetInterface::class);
        $this->builderMock = $this->createMock(Build::class);
        $this->inputTypeValidatorMock = $this->createMock(InputTypeValidator::class);
        $this->formDataSerializerMock = $this->createMock(FormData::class);
        $this->attributeCodeValidatorMock = $this->createMock(AttributeCodeValidator::class);
        $this->presentationMock = $this->createMock(Presentation::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->layoutFactoryMock = $this->createMock(LayoutFactory::class);
        $this->buildFactoryMock = $this->getMockBuilder(BuildFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeFactoryMock = $this->getMockBuilder(AttributeFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorFactoryMock = $this->getMockBuilder(ValidatorFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectMock = $this->getMockBuilder(ResultRedirect::class)
            ->onlyMethods(['setPath'])
            ->addMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productAttributeMock = $this->getMockBuilder(ProductAttributeInterface::class)
            ->onlyMethods(
                [
                    'getBackendType',
                    'getFrontendClass'
                ]
            )->addMethods(
                [
                    'getId',
                    'get',
                    'getBackendTypeByInput',
                    'getDefaultValueByInput',
                    'addData',
                    'save'
                ]
            )->getMockForAbstractClass();
        $this->buildFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->builderMock);
        $this->validatorFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->inputTypeValidatorMock);
        $this->attributeFactoryMock
            ->method('create')
            ->willReturn($this->productAttributeMock);
    }

    /**
     * @inheritdoc
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(Save::class, [
            'context' => $this->contextMock,
            'attributeLabelCache' => $this->attributeLabelCacheMock,
            'coreRegistry' => $this->coreRegistryMock,
            'resultPageFactory' => $this->resultPageFactoryMock,
            'buildFactory' => $this->buildFactoryMock,
            'filterManager' => $this->filterManagerMock,
            'productHelper' => $this->productHelperMock,
            'attributeFactory' => $this->attributeFactoryMock,
            'validatorFactory' => $this->validatorFactoryMock,
            'groupCollectionFactory' => $this->groupCollectionFactoryMock,
            'layoutFactory' => $this->layoutFactoryMock,
            'formDataSerializer' => $this->formDataSerializerMock,
            'attributeCodeValidator' => $this->attributeCodeValidatorMock,
            'presentation' => $this->presentationMock,
            '_session' => $this->sessionMock
        ]);
    }

    public function testExecuteWithEmptyData()
    {
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', ''],
            ]);
        $this->formDataSerializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with('')
            ->willReturn([]);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn([]);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->any())
            ->method('setPath')
            ->willReturnSelf();

        $this->assertInstanceOf(ResultRedirect::class, $this->getModel()->execute());
    }

    public function testExecuteSaveFrontendClass()
    {
        $data = [
            'frontend_input' => 'test_frontend_input',
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', ''],
                ['set', null, 1],
                ['attribute_code', null, 'test_attribute_code'],
            ]);
        $this->formDataSerializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with('')
            ->willReturn([]);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);
        $this->inputTypeValidatorMock->expects($this->any())
            ->method('isValid')
            ->with($data['frontend_input'])
            ->willReturn(true);
        $this->presentationMock->expects($this->once())
            ->method('convertPresentationDataToInputType')
            ->willReturn($data);
        $this->productHelperMock->expects($this->once())
            ->method('getAttributeSourceModelByInputType')
            ->with($data['frontend_input'])
            ->willReturn(null);
        $this->productHelperMock->expects($this->once())
            ->method('getAttributeBackendModelByInputType')
            ->with($data['frontend_input'])
            ->willReturn(null);
        $this->productAttributeMock->expects($this->once())
            ->method('getBackendTypeByInput')
            ->with($data['frontend_input'])
            ->willReturnSelf('test_backend_type');
        $this->productAttributeMock->expects($this->once())
            ->method('getDefaultValueByInput')
            ->with($data['frontend_input'])
            ->willReturn(null);
        $this->productAttributeMock->expects($this->once())
            ->method('getBackendType')
            ->willReturn('static');
        $this->productAttributeMock->expects($this->once())
            ->method('getFrontendClass')
            ->willReturn('static');
        $this->resultFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->any())
            ->method('setPath')
            ->willReturnSelf();

        $this->assertInstanceOf(ResultRedirect::class, $this->getModel()->execute());
    }

    public function testExecute()
    {
        $data = [
            'new_attribute_set_name' => 'Test attribute set name',
            'frontend_input' => 'test_frontend_input',
        ];
        $this->filterManagerMock
            ->method('stripTags')
            ->willReturn('Test attribute set name');
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', ''],
            ]);
        $this->formDataSerializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with('')
            ->willReturn([]);
        $this->productAttributeMock
            ->method('getId')
            ->willReturn(1);
        $this->productAttributeMock
            ->method('getAttributeCode')
            ->willReturn('test_code');
        $this->attributeCodeValidatorMock
            ->method('isValid')
            ->with('test_code')
            ->willReturn(true);
        $this->requestMock->expects($this->once())
            ->method('getPostValue')
            ->willReturn($data);
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->any())
            ->method('setPath')
            ->willReturnSelf();
        $this->builderMock->expects($this->once())
            ->method('setEntityTypeId')
            ->willReturnSelf();
        $this->builderMock->expects($this->once())
            ->method('setSkeletonId')
            ->willReturnSelf();
        $this->builderMock->expects($this->once())
            ->method('setName')
            ->willReturnSelf();
        $this->builderMock->expects($this->once())
            ->method('getAttributeSet')
            ->willReturn($this->attributeSetMock);
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['set', null, 1],
                ['attribute_code', null, 'test_attribute_code']
            ]);
        $this->inputTypeValidatorMock->expects($this->once())
            ->method('getMessages')
            ->willReturn([]);

        $this->assertInstanceOf(ResultRedirect::class, $this->getModel()->execute());
    }

    /**
     * @throws NotFoundException
     */
    public function testExecuteWithOptionsDataError()
    {
        $serializedOptions = '{"key":"value"}';
        $message = "The attribute couldn't be saved due to an error. Verify your information and try again. "
            . "If the error persists, please try again later.";

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, true],
                ['serialized_options', '[]', $serializedOptions],
            ]);
        $this->formDataSerializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with($serializedOptions)
            ->willThrowException(new \InvalidArgumentException('Some exception'));
        $this->messageManager
            ->expects($this->once())
            ->method('addErrorMessage')
            ->with($message);
        $this->addReturnResultConditions('catalog/*/edit', ['_current' => true], ['error' => true]);

        $this->getModel()->execute();
    }

    /**
     * @param string $path
     * @param array $params
     * @param array $response
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function addReturnResultConditions(string $path = '', array $params = [], array $response = [])
    {
        $layoutMock = $this->getMockBuilder(LayoutInterface::class)
            ->setMethods(['initMessages', 'getMessagesBlock'])
            ->getMockForAbstractClass();
        $this->layoutFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with()
            ->willReturn($layoutMock);
        $layoutMock
            ->method('initMessages')
            ->with();
        $messageBlockMock = $this->getMockBuilder(Messages::class)
            ->disableOriginalConstructor()
            ->getMock();
        $layoutMock
            ->expects($this->once())
            ->method('getMessagesBlock')
            ->willReturn($messageBlockMock);
        $messageBlockMock
            ->expects($this->once())
            ->method('getGroupedHtml')
            ->willReturn('message1');
        $this->resultFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->willReturn($this->redirectMock);
        $response  = array_merge($response, [
            'messages' => ['message1'],
            'params' => $params,
        ]);
        $this->redirectMock
            ->expects($this->once())
            ->method('setData')
            ->with($response)
            ->willReturnSelf();
    }
}
