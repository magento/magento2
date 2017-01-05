<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Price;

use Magento\Catalog\Api\Data\TierPriceInterface;

/**
 * Class TierPriceValidatorTest.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TierPriceValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ProductIdLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $productIdLocator;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filterBuilder;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerGroupRepository;

    /**
     * @var \Magento\Store\Api\WebsiteRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $websiteRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Price\TierPricePersistence|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tierPricePersistence;

    /**
     * @var \Magento\Catalog\Api\Data\TierPriceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tierPriceInterface;

    /**
     * @var \Magento\Catalog\Model\Product\Price\TierPriceValidator
     */
    private $model;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->productIdLocator = $this->getMockForAbstractClass(
            \Magento\Catalog\Model\ProductIdLocatorInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['retrieveProductIdsBySkus']
        );
        $this->searchCriteriaBuilder = $this->getMock(
            \Magento\Framework\Api\SearchCriteriaBuilder::class,
            ['addFilters', 'create'],
            [],
            '',
            false
        );
        $this->filterBuilder = $this->getMock(
            \Magento\Framework\Api\FilterBuilder::class,
            ['setField', 'setValue', 'create'],
            [],
            '',
            false
        );
        $this->customerGroupRepository = $this->getMockForAbstractClass(
            \Magento\Customer\Api\GroupRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getList']
        );
        $this->websiteRepository = $this->getMockForAbstractClass(
            \Magento\Store\Api\WebsiteRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getById']
        );
        $this->tierPricePersistence = $this->getMock(
            \Magento\Catalog\Model\Product\Price\TierPricePersistence::class,
            ['addFilters', 'create'],
            [],
            '',
            false
        );
        $this->tierPriceInterface = $this->getMockForAbstractClass(
            \Magento\Catalog\Api\Data\TierPriceInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getSku', 'getPrice', 'getPriceType', 'getQuantity', 'getWebsiteId', 'getCustomerGroup']
        );

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Price\TierPriceValidator::class,
            [
                'productIdLocator' => $this->productIdLocator,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'filterBuilder' => $this->filterBuilder,
                'customerGroupRepository' => $this->customerGroupRepository,
                'websiteRepository' => $this->websiteRepository,
                'tierPricePersistence' => $this->tierPricePersistence,
                'allowedProductTypes' => ['simple', 'virtual', 'bundle', 'downloadable'],
            ]
        );
    }

    /**
     * Test validateSkus method.
     *
     * @return void
     */
    public function testValidateSkus()
    {
        $skus = ['sku_1', 'sku_2'];
        $idsBySku = [
            'sku_1' => [1 => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE],
            'sku_2' => [2 => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL],
        ];
        $this->productIdLocator
            ->expects($this->once())
            ->method('retrieveProductIdsBySkus')
            ->with($skus)
            ->willReturn($idsBySku);
        $this->model->validateSkus($skus);
    }

    /**
     * Test validateSkus method throws exception.
     *
     * @expectedException \Magento\Framework\Exception\NoSuchEntityException
     * @expectedExceptionMessage Requested products don't exist: sku_1, sku_2
     */
    public function testValidateSkusWithException()
    {
        $skus = ['sku_1', 'sku_2'];
        $idsBySku = [
            'sku_1' => [1 => 'grouped'],
            'sku_2' => [2 => 'configurable'],
        ];
        $this->productIdLocator
            ->expects($this->once())
            ->method('retrieveProductIdsBySkus')
            ->with($skus)
            ->willReturn($idsBySku);
        $this->model->validateSkus($skus);
    }

    /**
     * Test validatePrices method.
     *
     * @return void
     */
    public function testValidatePrices()
    {
        $sku = 'sku_1';
        $idsBySku = [
            'sku_1' => [1 => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE],
            'sku_2' => [2 => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL],
        ];
        $productPrice = 15;
        $this->tierPriceInterface->expects($this->exactly(8))->method('getSku')->willReturn($sku);
        $this->productIdLocator->expects($this->exactly(2))->method('retrieveProductIdsBySkus')->willReturn($idsBySku);
        $this->tierPriceInterface->expects($this->exactly(2))->method('getPrice')->willReturn($productPrice);
        $this->tierPriceInterface
            ->expects($this->exactly(2))
            ->method('getPriceType')
            ->willReturn(TierPriceInterface::PRICE_TYPE_FIXED);
        $this->tierPriceInterface->expects($this->exactly(3))->method('getQuantity')->willReturn(2);
        $this->checkWebsite($this->tierPriceInterface);
        $this->checkGroup($this->tierPriceInterface);
        $this->model->validatePrices([$this->tierPriceInterface], []);
    }

    /**
     * Test validatePrices method with downloadable product.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid attribute sku: .
     */
    public function testValidatePricesWithDownloadableProduct()
    {
        $this->tierPriceInterface->expects($this->exactly(2))->method('getSku')->willReturn(null);
        $this->model->validatePrices([$this->tierPriceInterface], []);
    }

    /**
     * Test validatePrices method with negative price.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid attribute Price: -15.
     */
    public function testValidatePricesWithNegativePrice()
    {
        $negativePrice = -15;
        $sku = 'sku_1';
        $idsBySku = [
            'sku_1' => [1 => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE],
            'sku_2' => [2 => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL],
        ];
        $this->tierPriceInterface->expects($this->exactly(3))->method('getSku')->willReturn($sku);
        $this->productIdLocator->expects($this->exactly(2))->method('retrieveProductIdsBySkus')->willReturn($idsBySku);
        $this->tierPriceInterface->expects($this->exactly(3))->method('getPrice')->willReturn($negativePrice);
        $this->model->validatePrices([$this->tierPriceInterface], []);
    }

    /**
     * Test validatePrices method with bundle product and fixed price.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid attribute Price Type: fixed.
     */
    public function testValidatePricesWithBundleProductAndFixedPrice()
    {
        $sku = 'sku_1';
        $idsBySku = [
            'sku_1' => [1 => \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE],
        ];
        $productPrice = 15;
        $this->tierPriceInterface->expects($this->exactly(4))->method('getSku')->willReturn($sku);
        $this->productIdLocator->expects($this->exactly(2))->method('retrieveProductIdsBySkus')->willReturn($idsBySku);
        $this->tierPriceInterface->expects($this->exactly(2))->method('getPrice')->willReturn($productPrice);
        $this->tierPriceInterface
            ->expects($this->exactly(4))
            ->method('getPriceType')
            ->willReturn(TierPriceInterface::PRICE_TYPE_FIXED);
        $this->model->validatePrices([$this->tierPriceInterface], []);
    }

    /**
     * Test validatePrices method with zero quantity.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid attribute Quantity: 0.
     */
    public function testValidatePricesWithZeroQty()
    {
        $sku = 'sku_1';
        $idsBySku = [
            'sku_1' => [1 => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL],
        ];
        $productPrice = 15;
        $this->tierPriceInterface->expects($this->exactly(4))->method('getSku')->willReturn($sku);
        $this->productIdLocator->expects($this->exactly(2))->method('retrieveProductIdsBySkus')->willReturn($idsBySku);
        $this->tierPriceInterface->expects($this->exactly(2))->method('getPrice')->willReturn($productPrice);
        $this->tierPriceInterface
            ->expects($this->exactly(2))
            ->method('getPriceType')
            ->willReturn(TierPriceInterface::PRICE_TYPE_FIXED);
        $this->tierPriceInterface->expects($this->exactly(2))->method('getQuantity')->willReturn(0);
        $this->model->validatePrices([$this->tierPriceInterface], []);
    }

    /**
     * Test validatePrices method without website.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Invalid attribute website_id: 15.
     */
    public function testValidatePricesWithoutWebsite()
    {
        $sku = 'sku_1';
        $idsBySku = [
            'sku_1' => [1 => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL],
        ];
        $productPrice = 15;
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->tierPriceInterface->expects($this->exactly(4))->method('getSku')->willReturn($sku);
        $this->productIdLocator->expects($this->exactly(2))->method('retrieveProductIdsBySkus')->willReturn($idsBySku);
        $this->tierPriceInterface->expects($this->exactly(2))->method('getPrice')->willReturn($productPrice);
        $this->tierPriceInterface
            ->expects($this->exactly(2))
            ->method('getPriceType')
            ->willReturn(TierPriceInterface::PRICE_TYPE_FIXED);
        $this->tierPriceInterface->expects($this->once())->method('getQuantity')->willReturn(2);
        $this->websiteRepository->expects($this->once())->method('getById')->willThrowException($exception);
        $this->tierPriceInterface->expects($this->exactly(2))->method('getWebsiteId')->willReturn(15);
        $this->model->validatePrices([$this->tierPriceInterface], []);
    }

    /**
     * Test validatePrices method not unique.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage We found a duplicate website, tier price, customer
     * group and quantity: Customer Group = retailer, Website Id = 2, Quantity = 2.
     */
    public function testValidatePricesNotUnique()
    {
        $sku = 'sku_1';
        $idsBySku = [
            'sku_1' => [1 => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL],
        ];
        $productPrice = 15;
        $this->tierPriceInterface->expects($this->exactly(8))->method('getSku')->willReturn($sku);
        $this->productIdLocator->expects($this->exactly(2))->method('retrieveProductIdsBySkus')->willReturn($idsBySku);
        $this->tierPriceInterface->expects($this->exactly(2))->method('getPrice')->willReturn($productPrice);
        $this->tierPriceInterface
            ->expects($this->exactly(2))
            ->method('getPriceType')
            ->willReturn(TierPriceInterface::PRICE_TYPE_FIXED);
        $website = $this->getMockForAbstractClass(
            \Magento\Store\Api\Data\WebsiteInterface::class,
            [],
            '',
            false
        );
        $this->tierPriceInterface
            ->expects($this->exactly(5))
            ->method('getWebsiteId')
            ->willReturnOnConsecutiveCalls(1, 0, 0, 1, 2);
        $this->websiteRepository->expects($this->once())->method('getById')->willReturn($website);
        $this->tierPriceInterface->expects($this->exactly(4))->method('getQuantity')->willReturn(2);
        $this->tierPriceInterface->expects($this->exactly(3))->method('getCustomerGroup')->willReturn('retailer');
        $this->model->validatePrices([$this->tierPriceInterface], []);
    }

    /**
     * Test validatePrices method without group.
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage No such entity with Customer Group = wholesale.
     */
    public function testValidatePricesWithoutGroup()
    {
        $sku = 'sku_1';
        $idsBySku = [
            'sku_1' => [1 => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL],
        ];
        $productPrice = 15;
        $this->tierPriceInterface->expects($this->exactly(8))->method('getSku')->willReturn($sku);
        $this->productIdLocator->expects($this->exactly(2))->method('retrieveProductIdsBySkus')->willReturn($idsBySku);
        $this->tierPriceInterface->expects($this->exactly(2))->method('getPrice')->willReturn($productPrice);
        $this->tierPriceInterface
            ->expects($this->exactly(2))
            ->method('getPriceType')
            ->willReturn(TierPriceInterface::PRICE_TYPE_FIXED);
        $this->tierPriceInterface->expects($this->exactly(3))->method('getQuantity')->willReturn(2);
        $this->checkWebsite($this->tierPriceInterface);
        $searchCriteria = $this->getMock(
            \Magento\Framework\Api\SearchCriteria::class,
            [],
            [],
            '',
            false
        );
        $searchResults = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\GroupSearchResultsInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getItems']
        );
        $this->tierPriceInterface->expects($this->exactly(3))->method('getCustomerGroup')->willReturn('wholesale');
        $this->searchCriteriaBuilder->expects($this->once())->method('addFilters')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setField')->with('customer_group_code')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setValue')->with('wholesale')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('create')->willReturnSelf();
        $this->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $this->customerGroupRepository
            ->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResults);
        $searchResults->expects($this->once())->method('getItems')->willReturn([]);
        $this->model->validatePrices([$this->tierPriceInterface], []);
    }

    /**
     * Check website.
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $price
     */
    private function checkWebsite(\PHPUnit_Framework_MockObject_MockObject $price)
    {
        $website = $this->getMockForAbstractClass(
            \Magento\Store\Api\Data\WebsiteInterface::class,
            [],
            '',
            false
        );
        $price->expects($this->exactly(3))->method('getWebsiteId')->willReturn(1);
        $this->websiteRepository->expects($this->once())->method('getById')->willReturn($website);
    }

    /**
     * Check group.
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $price
     */
    private function checkGroup(\PHPUnit_Framework_MockObject_MockObject $price)
    {
        $searchCriteria = $this->getMock(
            \Magento\Framework\Api\SearchCriteria::class,
            [],
            [],
            '',
            false
        );
        $searchResults = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\GroupSearchResultsInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getItems']
        );
        $group = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\GroupInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getCode', 'getId']
        );

        $price->expects($this->exactly(3))->method('getCustomerGroup')->willReturn('wholesale');
        $this->searchCriteriaBuilder->expects($this->once())->method('addFilters')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setField')->with('customer_group_code')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setValue')->with('wholesale')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('create')->willReturnSelf();
        $this->searchCriteriaBuilder
            ->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteria);
        $this->customerGroupRepository
            ->expects($this->once())
            ->method('getList')
            ->with($searchCriteria)
            ->willReturn($searchResults);
        $searchResults->expects($this->once())->method('getItems')->willReturn([$group]);
        $group->expects($this->once())->method('getCode')->willReturn('wholesale');
        $group->expects($this->once())->method('getId')->willReturn(4);
    }
}
