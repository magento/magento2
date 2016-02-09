<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Test\Unit\Model\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\ConfigurableProduct\Model\OptionRepository;
use Magento\ConfigurableProduct\Model\Product\SaveHandler;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableModel;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\ConfigurableFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use OAuthTest\Mocks\OAuth1\Service\Mock;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class SaveHandlerTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SaveHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OptionRepository|MockObject
     */
    private $optionRepository;

    /**
     * @var ConfigurableFactory|MockObject
     */
    private $configurableFactory;

    /**
     * @var Configurable|MockObject
     */
    private $configurable;

    /**
     * @var ProductAttributeRepositoryInterface|MockObject
     */
    private $productAttributeRepository;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder|MockObject
     */
    private $filterBuilder;

    /**
     * @var LinkManagementInterface|MockObject
     */
    private $linkManagement;

    /**
     * @var ProductRepository|MockObject
     */
    private $productRepository;

    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->optionRepository = $this->getMockBuilder(OptionRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['save', 'getList', 'deleteById'])
            ->getMock();

        $this->initConfigurableFactoryMock();

        $this->linkManagement = $this->getMock(LinkManagementInterface::class);

        $this->productAttributeRepository = $this->getMock(ProductAttributeRepositoryInterface::class);

        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['addFilters', 'create', '__wakeup'])
            ->getMock();

        $this->filterBuilder = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->setMethods(['setValue', 'setField', 'setConditionType', 'create', '__wakeup'])
            ->getMock();

        $this->productRepository = $this->getMockBuilder(ProductRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getList', '__wakeup'])
            ->getMock();

        $this->saveHandler = new SaveHandler(
            $this->optionRepository,
            $this->configurableFactory,
            $this->productAttributeRepository,
            $this->linkManagement,
            $this->productRepository,
            $this->searchCriteriaBuilder,
            $this->filterBuilder
        );
    }

    /**
     * @covers \Magento\ConfigurableProduct\Model\Product\SaveHandler::execute
     */
    public function testExecuteWithInvalidProductType()
    {
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getExtensionAttributes'])
            ->getMock();

        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn('simple');

        $product->expects(static::never())
            ->method('getExtensionAttributes');

        $entity = $this->saveHandler->execute('Entity', $product);
        static::assertSame($product, $entity);
    }

    /**
     * @covers \Magento\ConfigurableProduct\Model\Product\SaveHandler::execute
     */
    public function testExecuteWithEmptyExtensionAttributes()
    {
        $sku = 'test';
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getExtensionAttributes', 'getSku'])
            ->getMock();

        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(ConfigurableModel::TYPE_CODE);
        $product->expects(static::exactly(2))
            ->method('getSku')
            ->willReturn($sku);

        $extensionAttributes = $this->getMockBuilder(PaymentExtensionAttributes::class)
            ->setMethods(['getConfigurableProductOptions', 'getConfigurableProductLinks'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $extensionAttributes->expects(static::once())
            ->method('getConfigurableProductOptions')
            ->willReturn([]);
        $extensionAttributes->expects(static::once())
            ->method('getConfigurableProductLinks')
            ->willReturn([]);

        $this->optionRepository->expects(static::once())
            ->method('getList')
            ->with($sku)
            ->willReturn([]);
        $this->optionRepository->expects(static::never())
            ->method('deleteById');

        $this->productAttributeRepository->expects(static::never())
            ->method('get');

        $searchCriteria = $this->buildSearchCriteria([]);
        $list = $this->getMock(ProductSearchResultsInterface::class);
        $this->productRepository->expects(static::once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($list);
        $list->expects(static::once())
            ->method('getItems')
            ->willReturn([]);

        $this->linkManagement->expects(static::once())
            ->method('getChildren')
            ->with($sku)
            ->willReturn([]);
        $this->linkManagement->expects(static::never())
            ->method('removeChild');

        $entity = $this->saveHandler->execute('Entity', $product);
        static::assertSame($product, $entity);
    }

    /**
     * @covers \Magento\ConfigurableProduct\Model\Product\SaveHandler::execute
     */
    public function testExecute()
    {
        $attributeId = 90;
        $sku = 'config-1';
        $id = 25;
        $linkProductSku = 'link-product';
        $configurableProductLinks = [1, 2, 3];

        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTypeId', 'getSku', 'getData', 'getExtensionAttributes'])
            ->getMock();
        $product->expects(static::once())
            ->method('getTypeId')
            ->willReturn(ConfigurableModel::TYPE_CODE);
        $product->expects(static::exactly(4))
            ->method('getSku')
            ->willReturn($sku);

        $extensionAttributes = $this->getMockBuilder(PaymentExtensionAttributes::class)
            ->setMethods(['getConfigurableProductOptions', 'getConfigurableProductLinks'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $product->expects(static::once())
            ->method('getExtensionAttributes')
            ->willReturn($extensionAttributes);

        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAttributeId', 'loadByProductAndAttribute'])
            ->getMock();
        $this->processSaveOptions($attribute, $product, $attributeId, $sku, $id);

        $option = $this->getMockForAbstractClass(OptionInterface::class);
        $option->expects(static::once())
            ->method('getId')
            ->willReturn($id);

        $list = [$option];
        $this->optionRepository->expects(static::once())
            ->method('getList')
            ->with($sku)
            ->willReturn($list);
        $this->optionRepository->expects(static::never())
            ->method('deleteById');

        $configurableAttributes = [
            $attribute
        ];
        $extensionAttributes->expects(static::once())
            ->method('getConfigurableProductOptions')
            ->willReturn($configurableAttributes);

        $extensionAttributes->expects(static::once())
            ->method('getConfigurableProductLinks')
            ->willReturn($configurableProductLinks);

        $this->configurable->expects(static::once())
            ->method('saveProducts')
            ->with($product, $configurableProductLinks);

        $searchCriteria = $this->buildSearchCriteria($configurableProductLinks);

        $this->processDeleteOldLinks($searchCriteria, $sku, $linkProductSku);

        $entity = $this->saveHandler->execute('Entity', $product);
        static::assertSame($product, $entity);
    }

    /**
     * Init mock object for configurable factory
     *
     * @return void
     */
    private function initConfigurableFactoryMock()
    {
        $this->configurable = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $this->configurableFactory = $this->getMockBuilder(ConfigurableFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->configurableFactory->expects(static::any())
            ->method('create')
            ->willReturn($this->configurable);
    }

    /**
     * Get mock for search criteria
     *
     * @param array $ids
     * @return MockObject
     */
    private function buildSearchCriteria(array $ids)
    {
        $this->filterBuilder->expects(static::once())
            ->method('setField')
            ->willReturnSelf();
        $this->filterBuilder->expects(static::once())
            ->method('setConditionType')
            ->with('in')
            ->willReturnSelf();
        $this->filterBuilder->expects(static::once())
            ->method('setValue')
            ->with($ids)
            ->willReturnSelf();
        $this->filterBuilder->expects(static::once())
            ->method('create');

        $this->searchCriteriaBuilder->expects(static::once())
            ->method('addFilters')
            ->willReturnSelf();
        $searchCriteria = $this->getMock(SearchCriteriaInterface::class);
        $this->searchCriteriaBuilder->expects(static::once())
            ->method('create')
            ->willReturn($searchCriteria);
        return $searchCriteria;
    }

    /**
     * Mock for options save
     *
     * @param MockObject $attribute
     * @param MockObject $product
     * @param $attributeId
     * @param $sku
     * @param $id
     * @return void
     */
    private function processSaveOptions(MockObject $attribute, MockObject $product, $attributeId, $sku, $id)
    {
        $attribute->expects(static::once())
            ->method('getAttributeId')
            ->willReturn($attributeId);

        $eavAttribute = $this->getMock(ProductAttributeInterface::class);
        $this->productAttributeRepository->expects(static::once())
            ->method('get')
            ->with($attributeId)
            ->willReturn($eavAttribute);
        $attribute->expects(static::once())
            ->method('loadByProductAndAttribute')
            ->with($product, $eavAttribute)
            ->willReturnSelf();

        $this->optionRepository->expects(static::once())
            ->method('save')
            ->with($sku, $attribute)
            ->willReturn($id);
    }

    /**
     * Mock for delete product links
     *
     * @param MockObject $searchCriteria
     * @param $sku
     * @param $linkProductSku
     * @return void
     */
    private function processDeleteOldLinks(MockObject $searchCriteria, $sku, $linkProductSku)
    {
        $list = $this->getMock(ProductSearchResultsInterface::class);
        $this->productRepository->expects(static::once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($list);

        $product1 = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSku'])
            ->getMock();
        $product1->expects(static::once())
            ->method('getSku')
            ->willReturn('config-1');
        $product2 = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSku'])
            ->getMock();
        $product2->expects(static::once())
            ->method('getSku')
            ->willReturn('config-2');

        $items = [$product1, $product2];
        $list->expects(static::once())
            ->method('getItems')
            ->willReturn($items);

        $linkProduct = $this->getMock(ProductInterface::class);
        $links = [$linkProduct];
        $this->linkManagement->expects(static::once())
            ->method('getChildren')
            ->with($sku)
            ->willReturn($links);
        $linkProduct->expects(static::exactly(2))
            ->method('getSku')
            ->willReturn($linkProductSku);
        $this->linkManagement->expects(static::once())
            ->method('removeChild')
            ->with($sku, $linkProductSku);
    }
}
