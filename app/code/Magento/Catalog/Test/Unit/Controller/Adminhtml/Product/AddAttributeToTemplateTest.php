<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\AttributeSetRepositoryInterface;
use Magento\Catalog\Controller\Adminhtml\Product\AddAttributeToTemplate;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Api\Data\AttributeGroupInterfaceFactory;
use Magento\Eav\Api\Data\AttributeGroupSearchResultsInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AddAttributeToTemplateTest extends TestCase
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
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var ProductBuilder|MockObject
     */
    private $productBuilderMock;

    /**
     * @var JsonFactory|MockObject
     */
    private $resultJsonFactoryMock;

    /**
     * @var RequestInterface|MockObject
     */
    private $requestMock;

    /**
     * @var AttributeSetRepositoryInterface|MockObject
     */
    private $attributeSetRepositoryMock;

    /**
     * @var AttributeSetInterface|MockObject
     */
    private $attributeSetInterfaceMock;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var SearchCriteria|MockObject
     */
    private $searchCriteriaMock;

    /**
     * @var AttributeGroupRepositoryInterface|MockObject
     */
    private $attributeGroupRepositoryMock;

    /**
     * @var AttributeGroupSearchResultsInterface|MockObject
     */
    private $attributeGroupSearchResultsMock;

    /**
     * @var AttributeGroupInterfaceFactory|MockObject
     */
    private $attributeGroupInterfaceFactoryMock;

    /**
     * @var AttributeGroupInterface|MockObject
     */
    private $attributeGroupInterfaceMock;

    /**
     * @var Json|MockObject
     */
    private $jsonMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->createMock(Context::class);
        $this->productBuilderMock = $this->createMock(ProductBuilder::class);
        $this->resultJsonFactoryMock = $this->createPartialMock(JsonFactory::class, ['create']);
        $this->requestMock = $this->createMock(RequestInterface::class);
        $this->contextMock->expects($this->once())
            ->method('getRequest')
            ->willReturn($this->requestMock);
        $this->attributeSetRepositoryMock = $this->createMock(AttributeSetRepositoryInterface::class);
        $this->attributeSetInterfaceMock = $this->createMock(AttributeSetInterface::class);
        $this->searchCriteriaBuilderMock = $this->createPartialMock(
            SearchCriteriaBuilder::class,
            ['addFilter', 'create', 'setPageSize', 'addSortOrder']
        );
        $this->searchCriteriaMock = $this->createMock(SearchCriteria::class);
        $this->attributeGroupRepositoryMock = $this->createMock(AttributeGroupRepositoryInterface::class);
        $this->attributeGroupSearchResultsMock = $this->createMock(AttributeGroupSearchResultsInterface::class);
        $this->attributeGroupInterfaceFactoryMock = $this->createPartialMock(
            AttributeGroupInterfaceFactory::class,
            ['create']
        );
        $this->attributeGroupInterfaceMock = $this->createMock(AttributeGroupInterface::class);
        $this->jsonMock = $this->createMock(Json::class);

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
