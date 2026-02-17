<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Attribute;

use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Entity\Attribute;
use Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation;
use Magento\Catalog\Model\Product\AttributeSet\Build;
use Magento\Catalog\Model\Product\AttributeSet\BuildFactory;
use Magento\Catalog\Model\Product\Url;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as ResourceAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\AttributeTest;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator as InputTypeValidator;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Eav\Model\Validator\Attribute\Code as AttributeCodeValidator;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Serialize\Serializer\FormData;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection as AttributeGroupCollection;
use Magento\Eav\Model\Entity\Attribute\Group as AttributeGroup;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Exception\LocalizedException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class SaveTest extends AttributeTest
{
    use MockCreationTrait;
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
     * @var ResultJson|MockObject
     */
    private $jsonResultMock;

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
     * @var Attribute|MockObject
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
        $this->filterManagerMock = $this->createPartialMockWithReflection(FilterManager::class, ['stripTags']);
        $this->productHelperMock = $this->createMock(ProductHelper::class);
        $this->attributeSetMock = $this->createMock(AttributeSetInterface::class);
        $this->builderMock = $this->createMock(Build::class);
        $this->inputTypeValidatorMock = $this->createMock(InputTypeValidator::class);
        $this->formDataSerializerMock = $this->createMock(FormData::class);
        $this->attributeCodeValidatorMock = $this->createMock(AttributeCodeValidator::class);
        $this->presentationMock = $this->createMock(Presentation::class);
        $this->sessionMock = $this->createMock(Session::class);
        $this->layoutFactoryMock = $this->createMock(LayoutFactory::class);
        $this->buildFactoryMock = $this->createPartialMock(BuildFactory::class, ['create']);
        $this->attributeFactoryMock = $this->createPartialMock(AttributeFactory::class, ['create']);
        $this->validatorFactoryMock = $this->createPartialMock(ValidatorFactory::class, ['create']);
        $this->groupCollectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->redirectMock = $this->createMock(ResultRedirect::class);
        $this->jsonResultMock = $this->createMock(ResultJson::class);
        $this->productAttributeMock = $this->createMock(Attribute::class);
           
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

    public function testConstructorFallbackUsesGlobalObjectManagerForFormDataSerializer()
    {
        $serialized = '';

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', $serialized],
            ]);
        $this->formDataSerializerMock
            ->expects($this->once())
            ->method('unserialize')
            ->with($serialized)
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

        $originalOm = null;
        $hadOriginal = true;
        try {
            $originalOm = ObjectManager::getInstance();
        } catch (\RuntimeException $e) {
            $hadOriginal = false;
        }
        $objectManagerMock = $this->createMock(ObjectManagerInterface::class);
        $objectManagerMock->method('get')->willReturnCallback(function ($type) {
            if ($type === FormData::class) {
                return $this->formDataSerializerMock;
            }
            if ($type === Presentation::class) {
                return $this->presentationMock;
            }
            return null;
        });
        ObjectManager::setInstance($objectManagerMock);

        try {
            $model = $this->objectManager->getObject(Save::class, [
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
                // Intentionally omit 'formDataSerializer' to trigger fallback
                'formDataSerializer' => null,
                'presentation' => $this->presentationMock,
                '_session' => $this->sessionMock
            ]);

            $this->assertInstanceOf(ResultRedirect::class, $model->execute());
        } finally {
            if ($hadOriginal && $originalOm instanceof ObjectManagerInterface) {
                ObjectManager::setInstance($originalOm);
            } else {
                // Keep the mock instance to avoid leaving ObjectManager uninitialized
                ObjectManager::setInstance($objectManagerMock);
            }
        }
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
            ->willReturnCallback(function ($arg) {
                return $arg;
            });
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
            ->willReturn('test_backend_type');
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
        $layoutMock = $this->createMock(Layout::class);
        $this->layoutFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with()
            ->willReturn($layoutMock);
        $layoutMock
            ->method('initMessages')
            ->with();
        $messageBlockMock = $this->createMock(Messages::class);
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
            ->willReturn($this->jsonResultMock);
        $response  = array_merge($response, [
            'messages' => ['message1'],
            'params' => $params,
        ]);
        $this->jsonResultMock
            ->expects($this->once())
            ->method('setData')
            ->with($response)
            ->willReturnSelf();
    }

    public function testAlreadyExistsExceptionOnNewAttributeSet()
    {
        $data = [
            'new_attribute_set_name' => 'Set A',
            'frontend_input' => 'text',
        ];

        $this->filterManagerMock->method('stripTags')->willReturn('Set A');
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', ''],
                ['set', null, 5],
            ]);
        $this->formDataSerializerMock->expects($this->once())->method('unserialize')->with('')->willReturn([]);
        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn($data);
        $this->builderMock->expects($this->once())->method('setEntityTypeId')->willReturnSelf();
        $this->builderMock->expects($this->once())->method('setSkeletonId')->with(5)->willReturnSelf();
        $this->builderMock->expects($this->once())->method('setName')->with('Set A')->willReturnSelf();
        $this->builderMock->expects($this->once())
            ->method('getAttributeSet')
            ->willThrowException(new AlreadyExistsException(__('exists')));
        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->any())->method('setPath')->willReturnSelf();

        $this->assertInstanceOf(ResultRedirect::class, $this->getModel()->execute());
    }

    public function testGenerateCodeFromFrontendLabelSetsAttributeCode()
    {
        $data = [
            'frontend_input' => 'text',
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', ''],
                ['frontend_label', null, ['My Label']],
            ]);
        $this->formDataSerializerMock->expects($this->once())->method('unserialize')->with('')->willReturn([]);
        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn($data);
        $this->inputTypeValidatorMock->expects($this->any())->method('isValid')->with('text')->willReturn(true);
        $this->presentationMock->expects($this->once())
            ->method('convertPresentationDataToInputType')
            ->willReturnCallback(function ($arg) {
                return $arg;
            });
        $this->productHelperMock->expects($this->once())
            ->method('getAttributeSourceModelByInputType')
            ->with('text')
            ->willReturn(null);
        $this->productHelperMock->expects($this->once())
            ->method('getAttributeBackendModelByInputType')
            ->with('text')
            ->willReturn(null);
        $this->productAttributeMock->expects($this->once())
            ->method('getDefaultValueByInput')
            ->with('text')
            ->willReturn(null);

        // Prepare Action->_objectManager to control generateCode formatting
        $urlMock = $this->createPartialMock(Url::class, ['formatUrlKey']);
        $urlMock->method('formatUrlKey')->with('My Label')->willReturn('my label');
        $omInterface = $this->createMock(ObjectManagerInterface::class);
        $omInterface->method('create')->with(Url::class)->willReturn($urlMock);

        $model = $this->getModel();
        $this->objectManager->setBackwardCompatibleProperty(
            $model,
            '_objectManager',
            $omInterface,
            Action::class
        );

        $this->productAttributeMock->expects($this->once())
            ->method('addData')
            ->with($this->callback(function ($arg) {
                return isset($arg['attribute_code']) && $arg['attribute_code'] === 'my_label';
            }));

        $this->resultFactoryMock->expects($this->any())->method('create')->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->any())->method('setPath')->willReturnSelf();

        $this->assertInstanceOf(ResultRedirect::class, $model->execute());
    }

    public function testEntityTypeCheckFailsWhenBackendModelProvided()
    {
        $data = [
            'frontend_input' => 'text',
            'backend_model' => 'Some\\Model',
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', ''],
                ['attribute_id', null, 1],
                ['attribute_code', null, 'test_attribute_code'],
            ]);
        $this->formDataSerializerMock->expects($this->once())->method('unserialize')->with('')->willReturn([]);
        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn($data);

        // Attribute model with load() and required getters
        $attributeWithLoad = $this->createPartialMock(
            ResourceAttribute::class,
            ['load','getId','getAttributeCode','getEntityTypeId']
        );
        $attributeWithLoad->method('load')->willReturnSelf();
        $attributeWithLoad->method('getId')->willReturn(1);
        $attributeWithLoad->method('getAttributeCode')->willReturn('test_attribute_code');
        $attributeWithLoad->method('getEntityTypeId')->willReturn(999);
        $localAttributeFactory = $this->createPartialMock(AttributeFactory::class, ['create']);
        $localAttributeFactory->method('create')->willReturn($attributeWithLoad);

        $this->inputTypeValidatorMock->method('isValid')->with('text')->willReturn(true);
        $this->presentationMock->method('convertPresentationDataToInputType')->willReturnCallback(function ($arg) {
            return $arg;
        });
        $this->resultFactoryMock->expects($this->once())->method('create')->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->any())->method('setPath')->willReturnSelf();

        $controller = $this->objectManager->getObject(Save::class, [
            'context' => $this->contextMock,
            'attributeLabelCache' => $this->attributeLabelCacheMock,
            'coreRegistry' => $this->coreRegistryMock,
            'resultPageFactory' => $this->resultPageFactoryMock,
            'buildFactory' => $this->buildFactoryMock,
            'filterManager' => $this->filterManagerMock,
            'productHelper' => $this->productHelperMock,
            'attributeFactory' => $localAttributeFactory,
            'validatorFactory' => $this->validatorFactoryMock,
            'groupCollectionFactory' => $this->groupCollectionFactoryMock,
            'layoutFactory' => $this->layoutFactoryMock,
            'formDataSerializer' => $this->formDataSerializerMock,
            'presentation' => $this->presentationMock,
            '_session' => $this->sessionMock
        ]);

        $this->assertInstanceOf(ResultRedirect::class, $controller->execute());
    }

    public function testResetDefaultOptionClearsDefaultValue()
    {
        $data = [
            'frontend_input' => 'select',
            'reset_is-default_option' => 1,
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', ''],
                ['attribute_code', null, 'code'],
            ]);
        $this->formDataSerializerMock->expects($this->once())->method('unserialize')->with('')->willReturn([]);
        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn($data);
        $this->inputTypeValidatorMock->method('isValid')->with('select')->willReturn(true);
        $this->presentationMock->method('convertPresentationDataToInputType')->willReturnCallback(function ($arg) {
            return $arg;
        });
        $this->productHelperMock->method('getAttributeSourceModelByInputType')->with('select')->willReturn(null);
        $this->productHelperMock->method('getAttributeBackendModelByInputType')->with('select')->willReturn(null);
        $this->productAttributeMock->method('getDefaultValueByInput')->with('select')->willReturn(null);

        $this->productAttributeMock->expects($this->once())
            ->method('addData')
            ->with($this->callback(function ($arg) {
                return array_key_exists('default_value', $arg) && $arg['default_value'] === null
                    && !array_key_exists('reset_is-default_option', $arg);
            }));
        $this->resultFactoryMock->expects($this->any())->method('create')->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->any())->method('setPath')->willReturnSelf();

        $this->assertInstanceOf(ResultRedirect::class, $this->getModel()->execute());
    }

    public function testDefaultOptionsCollectImplodedDefaultValue()
    {
        $data = [
            'frontend_input' => 'multiselect',
            'default' => ['0', '2', '3'],
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', ''],
                ['attribute_code', null, 'code'],
            ]);
        $this->formDataSerializerMock->expects($this->once())->method('unserialize')->with('')->willReturn([]);
        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn($data);
        $this->inputTypeValidatorMock->method('isValid')->with('multiselect')->willReturn(true);
        $this->presentationMock->method('convertPresentationDataToInputType')->willReturnCallback(function ($arg) {
            return $arg;
        });
        $this->productHelperMock->method('getAttributeSourceModelByInputType')->with('multiselect')->willReturn(null);
        $this->productHelperMock->method('getAttributeBackendModelByInputType')->with('multiselect')->willReturn(null);
        $this->productAttributeMock->method('getDefaultValueByInput')->with('multiselect')->willReturn(null);

        $this->productAttributeMock->expects($this->once())
            ->method('addData')
            ->with($this->callback(function ($arg) {
                return isset($arg['default_value']) && $arg['default_value'] === '2,3';
            }));
        $this->resultFactoryMock->expects($this->any())->method('create')->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->any())->method('setPath')->willReturnSelf();

        $this->assertInstanceOf(ResultRedirect::class, $this->getModel()->execute());
    }

    public function testGroupCollectionCreatesAndSavesGroup()
    {
        $data = [
            'frontend_input' => 'text',
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', ''],
                ['set', null, 5],
                ['group', null, 'group_code'],
                ['groupSortOrder', null, 7],
                ['groupName', null, 'Group Name'],
                ['attribute_code', null, 'code'],
            ]);
        $this->formDataSerializerMock->expects($this->once())->method('unserialize')->with('')->willReturn([]);
        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn($data);
        $this->inputTypeValidatorMock->method('isValid')->with('text')->willReturn(true);
        $this->presentationMock->method('convertPresentationDataToInputType')->willReturnCallback(function ($arg) {
            return $arg;
        });
        $this->productHelperMock->method('getAttributeSourceModelByInputType')->with('text')->willReturn(null);
        $this->productHelperMock->method('getAttributeBackendModelByInputType')->with('text')->willReturn(null);

        $localAttributeFactory = $this->createAttributeFactoryForGroupCollectionTest();
        $this->prepareGroupCollectionFactoryForGroupCreation();

        $this->resultFactoryMock->expects($this->any())->method('create')->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->any())->method('setPath')->willReturnSelf();

        $controller = $this->createControllerWithAttributeFactory($localAttributeFactory);

        $this->assertInstanceOf(ResultRedirect::class, $controller->execute());
    }

    public function testPopupReturnResultRedirectsToAddAttribute()
    {
        $data = [
            'frontend_input' => 'text',
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', ''],
                ['set', null, 5],
                ['group', null, null],
                ['popup', null, true],
                ['product', null, 123],
                ['product_tab', null, 'general'],
                ['attribute_code', null, 'code'],
            ]);
        $this->formDataSerializerMock->expects($this->once())->method('unserialize')->with('')->willReturn([]);
        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn($data);
        $this->inputTypeValidatorMock->method('isValid')->with('text')->willReturn(true);
        $this->presentationMock->method('convertPresentationDataToInputType')->willReturnCallback(function ($arg) {
            return $arg;
        });
        $this->productHelperMock->method('getAttributeSourceModelByInputType')->with('text')->willReturn(null);
        $this->productHelperMock->method('getAttributeBackendModelByInputType')->with('text')->willReturn(null);
        $this->productAttributeMock->method('getDefaultValueByInput')->with('text')->willReturn(null);
        $this->productAttributeMock->method('getId')->willReturn(10);
        $this->productAttributeMock->method('getAttributeCode')->willReturn('code');

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with('catalog/product/addAttribute', [
                'attributeId' => 123,
                'attribute' => 10,
                '_current' => true,
                'product_tab' => 'general',
            ])
            ->willReturnSelf();

        $this->assertInstanceOf(ResultRedirect::class, $this->getModel()->execute());
    }

    public function testBackParamReturnsEditRedirect()
    {
        $data = [
            'frontend_input' => 'text',
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', ''],
                ['set', null, 5],
                ['group', null, null],
                ['back', false, true],
                ['attribute_code', null, 'code'],
            ]);
        $this->formDataSerializerMock->expects($this->once())->method('unserialize')->with('')->willReturn([]);
        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn($data);
        $this->inputTypeValidatorMock->method('isValid')->with('text')->willReturn(true);
        $this->presentationMock->method('convertPresentationDataToInputType')->willReturnCallback(function ($arg) {
            return $arg;
        });
        $this->productHelperMock->method('getAttributeSourceModelByInputType')->with('text')->willReturn(null);
        $this->productHelperMock->method('getAttributeBackendModelByInputType')->with('text')->willReturn(null);
        $this->productAttributeMock->method('getDefaultValueByInput')->with('text')->willReturn(null);
        $this->productAttributeMock->method('getId')->willReturn(11);
        $this->productAttributeMock->method('getAttributeCode')->willReturn('code');

        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with('catalog/*/edit', [
                'attribute_id' => 11,
                '_current' => true,
            ])
            ->willReturnSelf();

        $this->assertInstanceOf(ResultRedirect::class, $this->getModel()->execute());
    }

    public function testSaveExceptionAddsErrorAndStoresData()
    {
        $data = [
            'frontend_input' => 'text',
        ];

        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', ''],
                ['attribute_code', null, 'code'],
            ]);
        $this->formDataSerializerMock->expects($this->once())->method('unserialize')->with('')->willReturn([]);
        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn($data);
        $this->inputTypeValidatorMock->method('isValid')->with('text')->willReturn(true);
        $this->presentationMock->method('convertPresentationDataToInputType')->willReturnCallback(function ($arg) {
            return $arg;
        });
        $this->productHelperMock->method('getAttributeSourceModelByInputType')->with('text')->willReturn(null);
        $this->productHelperMock->method('getAttributeBackendModelByInputType')->with('text')->willReturn(null);
        $this->productAttributeMock->method('getDefaultValueByInput')->with('text')->willReturn(null);
        $this->productAttributeMock->method('save')->willThrowException(new \Exception('fail'));

        $this->messageManager->expects($this->once())->method('addErrorMessage')->with('fail');
        // Do not assert on magic session setter
        $this->resultFactoryMock->expects($this->once())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->once())
            ->method('setPath')
            ->with('catalog/*/edit', [
                'attribute_id' => null,
                '_current' => true,
            ])
            ->willReturnSelf();

        $this->assertInstanceOf(ResultRedirect::class, $this->getModel()->execute());
    }

    public function testNewAttributeSetLocalizedExceptionAddsErrorMessage()
    {
        $data = [
            'new_attribute_set_name' => 'Name',
            'frontend_input' => 'text',
        ];

        $this->filterManagerMock->method('stripTags')->willReturn('Name');
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', ''],
                ['set', null, 1],
                ['attribute_code', null, 'code'],
            ]);
        $this->formDataSerializerMock->expects($this->once())->method('unserialize')->with('')->willReturn([]);
        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn($data);

        $this->builderMock->method('setEntityTypeId')->willReturnSelf();
        $this->builderMock->method('setSkeletonId')->with(1)->willReturnSelf();
        $this->builderMock->method('setName')->with('Name')->willReturnSelf();
        $this->builderMock->expects($this->once())
            ->method('getAttributeSet')
            ->willThrowException(new LocalizedException(__('bad')));

        $this->messageManager->expects($this->once())->method('addErrorMessage')->with('bad');
        $this->inputTypeValidatorMock->method('isValid')->with('text')->willReturn(true);
        $this->presentationMock->method('convertPresentationDataToInputType')->willReturnCallback(function ($arg) {
            return $arg;
        });
        $this->productHelperMock->method('getAttributeSourceModelByInputType')->with('text')->willReturn(null);
        $this->productHelperMock->method('getAttributeBackendModelByInputType')->with('text')->willReturn(null);
        $this->productAttributeMock->method('getDefaultValueByInput')->with('text')->willReturn(null);

        $this->resultFactoryMock->expects($this->any())->method('create')->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->any())->method('setPath')->willReturnSelf();

        $this->assertInstanceOf(ResultRedirect::class, $this->getModel()->execute());
    }

    public function testNewAttributeSetGenericExceptionAddsExceptionMessage()
    {
        $data = [
            'new_attribute_set_name' => 'Name',
            'frontend_input' => 'text',
        ];

        $this->filterManagerMock->method('stripTags')->willReturn('Name');
        $this->requestMock->expects($this->any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, null],
                ['serialized_options', '[]', ''],
                ['set', null, 1],
                ['attribute_code', null, 'code'],
            ]);
        $this->formDataSerializerMock->expects($this->once())->method('unserialize')->with('')->willReturn([]);
        $this->requestMock->expects($this->once())->method('getPostValue')->willReturn($data);

        $this->builderMock->method('setEntityTypeId')->willReturnSelf();
        $this->builderMock->method('setSkeletonId')->with(1)->willReturnSelf();
        $this->builderMock->method('setName')->with('Name')->willReturnSelf();
        $ex = new \Exception('boom');
        $this->builderMock->expects($this->once())
            ->method('getAttributeSet')
            ->willThrowException($ex);

        $this->messageManager->expects($this->once())->method('addExceptionMessage')
            ->with($ex, __('Something went wrong while saving the attribute.'));
        $this->inputTypeValidatorMock->method('isValid')->with('text')->willReturn(true);
        $this->presentationMock->method('convertPresentationDataToInputType')->willReturnCallback(function ($arg) {
            return $arg;
        });
        $this->productHelperMock->method('getAttributeSourceModelByInputType')->with('text')->willReturn(null);
        $this->productHelperMock->method('getAttributeBackendModelByInputType')->with('text')->willReturn(null);
        $this->productAttributeMock->method('getDefaultValueByInput')->with('text')->willReturn(null);

        $this->resultFactoryMock->expects($this->any())->method('create')->willReturn($this->redirectMock);
        $this->redirectMock->expects($this->any())->method('setPath')->willReturnSelf();

        $this->assertInstanceOf(ResultRedirect::class, $this->getModel()->execute());
    }

    private function createAttributeFactoryForGroupCollectionTest()
    {
        $attributeModel = $this->createPartialMock(
            ResourceAttribute::class,
            [
                'getDefaultValueByInput',
                'getBackendType',
                'getFrontendClass',
                'addData',
                'save',
                'setEntityTypeId',
                'setIsUserDefined',
                'getId'
            ]
        );
        $attributeModel->method('getDefaultValueByInput')->with('text')->willReturn(null);
        $attributeModel->method('addData')->willReturnSelf();
        $attributeModel->method('save')->willReturnSelf();
        $attributeModel->method('setEntityTypeId')->willReturnSelf();
        $attributeModel->method('setIsUserDefined')->willReturnSelf();
        $attributeModel->method('getId')->willReturn(null);

        $localAttributeFactory = $this->createPartialMock(AttributeFactory::class, ['create']);
        $localAttributeFactory->method('create')->willReturn($attributeModel);

        return $localAttributeFactory;
    }

    private function prepareGroupCollectionFactoryForGroupCreation()
    {
        $collectionMock = $this->createPartialMock(
            AttributeGroupCollection::class,
            [
                'setAttributeSetFilter',
                'addFieldToFilter',
                'setPageSize',
                'load',
                'getFirstItem'
            ]
        );
        $groupMock = $this->createPartialMock(AttributeGroup::class, ['getId','save']);
        $groupMock->expects($this->exactly(2))->method('getId')->willReturnOnConsecutiveCalls(null, 5);
        $groupMock->expects($this->once())->method('save')->willReturnSelf();

        $collectionMock->expects($this->once())->method('setAttributeSetFilter')->with(5)->willReturnSelf();
        $collectionMock->expects($this->once())
            ->method('addFieldToFilter')
            ->with('attribute_group_code', 'group_code')
            ->willReturnSelf();
        $collectionMock->expects($this->once())->method('setPageSize')->with(1)->willReturnSelf();
        $collectionMock->expects($this->once())->method('load')->willReturnSelf();
        $collectionMock->expects($this->once())->method('getFirstItem')->willReturn($groupMock);

        $this->groupCollectionFactoryMock->expects($this->once())->method('create')->willReturn($collectionMock);
    }

    private function createControllerWithAttributeFactory($localAttributeFactory)
    {
        return $this->objectManager->getObject(Save::class, [
            'context' => $this->contextMock,
            'attributeLabelCache' => $this->attributeLabelCacheMock,
            'coreRegistry' => $this->coreRegistryMock,
            'resultPageFactory' => $this->resultPageFactoryMock,
            'buildFactory' => $this->buildFactoryMock,
            'filterManager' => $this->filterManagerMock,
            'productHelper' => $this->productHelperMock,
            'attributeFactory' => $localAttributeFactory,
            'validatorFactory' => $this->validatorFactoryMock,
            'groupCollectionFactory' => $this->groupCollectionFactoryMock,
            'layoutFactory' => $this->layoutFactoryMock,
            'formDataSerializer' => $this->formDataSerializerMock,
            'presentation' => $this->presentationMock,
            '_session' => $this->sessionMock
        ]);
    }
}
