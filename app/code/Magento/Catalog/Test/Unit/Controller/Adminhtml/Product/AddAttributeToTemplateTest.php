<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product\AddAttributeToTemplate;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteria;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupSearchResultsInterface;
use Magento\Eav\Api\Data\AttributeGroupInterfaceFactory;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Framework\Controller\Result\Json;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddAttributeToTemplateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var AddAttributeToTemplate
     */
    private $controller;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $contextMock;

    /**
     * @var ProductBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productBuilderMock;

    /**
     * @var JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestMock;

    /**
     * @var AttributeSetRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeSetRepositoryMock;

    /**
     * @var AttributeSetInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeSetInterfaceMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var AttributeGroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeGroupRepositoryMock;

    /**
     * @var AttributeGroupSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeGroupSearchResultsMock;

    /**
     * @var AttributeGroupInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeGroupInterfaceFactoryMock;

    /**
     * @var AttributeGroupInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeGroupInterfaceMock;

    /**
     * @var Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonMock;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->productBuilderMock = $this->getMockBuilder(ProductBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->resultJsonFactoryMock = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getParam', 'setParam'])
            ->getMockForAbstractClass();
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->attributeSetRepositoryMock = $this->getMockBuilder(AttributeSetRepositoryInterface::class)
            ->setMethods(['get'])
            ->getMockForAbstractClass();
        $this->attributeSetInterfaceMock = $this->getMockBuilder(AttributeSetInterface::class)
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilter', 'create', 'setPageSize', 'addSortOrder'])
            ->getMockForAbstractClass();
        $this->searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeGroupRepositoryMock = $this->getMockBuilder(AttributeGroupRepositoryInterface::class)
            ->setMethods(['getList'])
            ->getMockForAbstractClass();
        $this->attributeGroupSearchResultsMock = $this->getMockBuilder(AttributeGroupSearchResultsInterface::class)
            ->setMethods(['getItems'])
            ->getMockForAbstractClass();
        $this->attributeGroupInterfaceFactoryMock = $this->getMockBuilder(AttributeGroupInterfaceFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeGroupInterfaceMock = $this->getMockBuilder(AttributeGroupInterface::class)
            ->setMethods(['getExtensionAttributes'])
            ->getMockForAbstractClass();
        $this->jsonMock = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = $this->objectManager->getObject(
            AddAttributeToTemplate::class,
            [
                'context' => $this->contextMock,
                'productBuilder' => $this->productBuilderMock,
                'resultJsonFactory' => $this->resultJsonFactoryMock,
            ]
        );

        $this->objectManager->setBackwardCompatibleProperty(
            $this->controller,
            'attributeSetRepository',
            $this->attributeSetRepositoryMock
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->controller,
            'searchCriteriaBuilder',
            $this->searchCriteriaBuilderMock
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->controller,
            'attributeGroupRepository',
            $this->attributeGroupRepositoryMock
        );
        $this->objectManager->setBackwardCompatibleProperty(
            $this->controller,
            'attributeGroupFactory',
            $this->attributeGroupInterfaceFactoryMock
        );
    }

    public function testExecuteWithoutAttributeGroupItems()
    {
        $groupCode = 'attributes';
        $groupName = 'Attributes';
        $groupSortOrder = '15';
        $templateId = '4';
        $attributeIds = [
            'selected' => ["178"],
            'total' => '1'
        ];

        $this->requestMock
            ->expects($this->any())
            ->method('getParam')
            ->willReturnMap(
                [
                    ['groupCode', null, $groupCode],
                    ['groupName', null, $groupName],
                    ['groupSortOrder', null, $groupSortOrder],
                    ['templateId', null, $templateId],
                    ['attributeIds', [], $attributeIds]
                ]
            );

        $this->attributeSetRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->attributeSetInterfaceMock);

        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('addFilter')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->any())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('setPageSize')
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->never())
            ->method('addSortOrder')
            ->willReturnSelf();

        $this->attributeGroupRepositoryMock->expects($this->once())
            ->method('getList')
            ->willReturn($this->attributeGroupSearchResultsMock);
        $this->attributeGroupSearchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn(null);

        $this->attributeGroupInterfaceFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->attributeGroupInterfaceMock);
        $this->attributeGroupInterfaceMock->expects($this->once())
            ->method('getExtensionAttributes')
            ->willThrowException(new LocalizedException(__('Could not get extension attributes')));

        $this->resultJsonFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->jsonMock);
        $this->jsonMock->expects($this->once())->method('setJsonData')
            ->willReturnSelf();

        $this->controller->execute();
    }
}
