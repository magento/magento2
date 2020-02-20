<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save;
use Magento\Eav\Model\Validator\Attribute\Code as AttributeCodeValidator;
use Magento\Framework\Serialize\Serializer\FormData;
use Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\AttributeTest;
use Magento\Catalog\Model\Product\AttributeSet\BuildFactory;
use Magento\Catalog\Model\Product\AttributeSet\Build;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Filter\FilterManager;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\LayoutFactory;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator as InputTypeValidator;
use Magento\Framework\View\LayoutInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveTest extends AttributeTest
{
    /**
     * @var BuildFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $buildFactoryMock;

    /**
     * @var FilterManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterManagerMock;

    /**
     * @var ProductHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productHelperMock;

    /**
     * @var AttributeFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeFactoryMock;

    /**
     * @var ValidatorFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validatorFactoryMock;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $groupCollectionFactoryMock;

    /**
     * @var LayoutFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutFactoryMock;

    /**
     * @var ResultRedirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;

    /**
     * @var AttributeSet|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeSetMock;

    /**
     * @var Build|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $builderMock;

    /**
     * @var InputTypeValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $inputTypeValidatorMock;

    /**
     * @var FormData|\PHPUnit_Framework_MockObject_MockObject
     */
    private $formDataSerializerMock;

    /**
     * @var ProductAttributeInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productAttributeMock;

    /**
     * @var AttributeCodeValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeCodeValidatorMock;

    protected function setUp()
    {
        parent::setUp();
        $this->buildFactoryMock = $this->getMockBuilder(BuildFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterManagerMock = $this->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productHelperMock = $this->getMockBuilder(ProductHelper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeFactoryMock = $this->getMockBuilder(AttributeFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->validatorFactoryMock = $this->getMockBuilder(ValidatorFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->layoutFactoryMock = $this->getMockBuilder(LayoutFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->redirectMock = $this->getMockBuilder(ResultRedirect::class)
            ->setMethods(['setData', 'setPath'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeSetMock = $this->getMockBuilder(AttributeSetInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->builderMock = $this->getMockBuilder(Build::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inputTypeValidatorMock = $this->getMockBuilder(InputTypeValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->formDataSerializerMock = $this->getMockBuilder(FormData::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeCodeValidatorMock = $this->getMockBuilder(AttributeCodeValidator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productAttributeMock = $this->getMockBuilder(ProductAttributeInterface::class)
            ->setMethods(['getId', 'get'])
            ->getMockForAbstractClass();

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
     * {@inheritdoc}
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
            'attributeCodeValidator' => $this->attributeCodeValidatorMock
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

    public function testExecute()
    {
        $data = [
            'new_attribute_set_name' => 'Test attribute set name',
            'frontend_input' => 'test_frontend_input',
        ];

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
     * @throws \Magento\Framework\Exception\NotFoundException
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
     * @return mixed
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
