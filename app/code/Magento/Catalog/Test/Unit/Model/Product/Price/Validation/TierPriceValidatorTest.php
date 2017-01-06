<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Price\Validation;

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
     * @var \Magento\Catalog\Model\Product\Price\InvalidSkuChecker|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invalidSkuChecker;

    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\Result|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validationResult;

    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator
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
        $this->filterBuilder->method('setField')->willReturnSelf();
        $this->filterBuilder->method('setValue')->willReturnSelf();
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
        $this->validationResult = $this->getMockBuilder(\Magento\Catalog\Model\Product\Price\Validation\Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invalidSkuChecker = $this->getMockBuilder(\Magento\Catalog\Model\Product\Price\InvalidSkuChecker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManager->getObject(
            \Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator::class,
            [
                'productIdLocator' => $this->productIdLocator,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'filterBuilder' => $this->filterBuilder,
                'customerGroupRepository' => $this->customerGroupRepository,
                'websiteRepository' => $this->websiteRepository,
                'tierPricePersistence' => $this->tierPricePersistence,
                'validationResult' => $this->validationResult,
                'invalidSkuChecker' => $this->invalidSkuChecker,
                'allowedProductTypes' => ['simple', 'virtual', 'bundle', 'downloadable'],
            ]
        );
    }

    /**
     * Test retrieveValidPrices method.
     *
     * @return void
     */
    public function testRetrieveValidPrices()
    {
        $sku = 'sku_1';
        $idsBySku = [
            'sku_1' => [1 => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE],
            'sku_2' => [2 => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL],
        ];
        $this->productIdLocator->expects($this->once())->method('retrieveProductIdsBySkus')->willReturn($idsBySku);
        $productPrice = 15;
        $this->tierPriceInterface->expects($this->exactly(10))->method('getSku')->willReturn($sku);
        $this->invalidSkuChecker->expects($this->once())->method('retrieveInvalidSkuList')->willReturn([]);
        $this->tierPriceInterface->expects($this->exactly(2))->method('getPrice')->willReturn($productPrice);
        $this->tierPriceInterface
            ->expects($this->exactly(2))
            ->method('getPriceType')
            ->willReturn(TierPriceInterface::PRICE_TYPE_FIXED);
        $this->tierPriceInterface->expects($this->exactly(3))->method('getQuantity')->willReturn(2);
        $this->checkWebsite($this->tierPriceInterface);
        $this->checkGroup($this->tierPriceInterface);
        $this->model->retrieveValidationResult([$this->tierPriceInterface], []);
    }

    /**
     * Test retrieveValidPrices method with downloadable product.
     */
    public function testRetrieveValidPricesWithDownloadableProduct()
    {
        $idsBySku = [
            'sku_1' => [1 => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE],
            'sku_2' => [2 => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL],
        ];
        $this->productIdLocator->expects($this->once())->method('retrieveProductIdsBySkus')->willReturn($idsBySku);
        $this->tierPriceInterface->expects($this->exactly(10))->method('getSku')->willReturn('sku_1');
        $this->invalidSkuChecker->expects($this->once())->method('retrieveInvalidSkuList')->willReturn([]);
        $productPrice = 15;
        $this->tierPriceInterface->expects($this->exactly(2))->method('getPrice')->willReturn($productPrice);
        $this->tierPriceInterface
            ->expects($this->exactly(2))
            ->method('getPriceType')
            ->willReturn(TierPriceInterface::PRICE_TYPE_FIXED);
        $this->tierPriceInterface->expects($this->exactly(3))->method('getQuantity')->willReturn(2);
        $this->checkWebsite($this->tierPriceInterface);
        $this->checkGroup($this->tierPriceInterface);
        $this->validationResult
            ->expects($this->never())
            ->method('addFailedItem');
        $this->model->retrieveValidationResult([$this->tierPriceInterface], []);
    }

    /**
     * Test method call with invalid values.
     */
    public function testRetrieveValidPricesWithInvalidCall()
    {
        $idsBySku = [
            'sku_1' => [1 => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE],
            'sku_2' => [2 => \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL],
            'invalid' => [3 => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE],
        ];

        $this->tierPriceInterface->expects($this->exactly(10))->method('getSku')->willReturn('sku_1');
        $this->invalidSkuChecker->expects($this->once())->method('retrieveInvalidSkuList')->willReturn(['invalid']);
        $this->productIdLocator->expects($this->once())->method('retrieveProductIdsBySkus')->willReturn($idsBySku);
        $this->validationResult
            ->expects($this->exactly(5))
            ->method('addFailedItem');
        $this->tierPriceInterface->expects($this->exactly(3))->method('getPrice')->willReturn('-90');
        $this->tierPriceInterface->expects($this->exactly(2))->method('getPriceType')->willReturn('unknown');
        $this->tierPriceInterface->expects($this->exactly(4))->method('getQuantity')->willReturn('-90');
        $this->websiteRepository->method('getById')
            ->willThrowException(new \Magento\Framework\Exception\NoSuchEntityException());
        $searchCriteria = $this->getMockForAbstractClass(
            \Magento\Framework\Api\SearchCriteriaInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['create']
        );
        $searchCriteria->method('create')->willReturnSelf();
        $this->searchCriteriaBuilder->method('addFilters')->willReturn($searchCriteria);
        $this->searchCriteriaBuilder->expects($this->once())->method('addFilters')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setField')->with('customer_group_code')->willReturnSelf();
        $this->filterBuilder->expects($this->once())->method('setValue')->willReturnSelf();
        $searchResults = $this->getMockForAbstractClass(
            \Magento\Customer\Api\Data\GroupSearchResultsInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getItems']
        );
        $this->filterBuilder->expects($this->atLeastOnce())->method('create')->willReturnSelf();
        $searchResults->expects($this->atLeastOnce())->method('getItems')->willReturn([]);
        $this->customerGroupRepository
            ->expects($this->atLeastOnce())
            ->method('getList')
            ->willReturn($searchResults);
        $this->model->retrieveValidationResult([$this->tierPriceInterface], []);
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
