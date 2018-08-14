<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\Attribute;

use Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save;
use Magento\Catalog\Test\Unit\Controller\Adminhtml\Product\AttributeTest;
use Magento\Catalog\Model\Product\AttributeSet\BuildFactory;
use Magento\Catalog\Model\Product\AttributeSet\Build;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\Filter\FilterManager;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\View\LayoutFactory;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator as InputTypeValidator;

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

        $this->buildFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->builderMock);
        $this->validatorFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->inputTypeValidatorMock);
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
        ]);
    }

    public function testExecuteWithEmptyData()
    {
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
}
