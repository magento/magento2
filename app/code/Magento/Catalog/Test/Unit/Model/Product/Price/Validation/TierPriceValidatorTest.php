<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Price\Validation;

use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor;
use Magento\Catalog\Model\Product\Price\Validation\Result;
use Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\GroupSearchResultsInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\AbstractSimpleObject;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for \Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TierPriceValidatorTest extends TestCase
{
    /**
     * @var TierPriceValidator
     */
    private $tierPriceValidator;

    /**
     * @var ProductIdLocatorInterface|MockObject
     */
    private $productIdLocator;

    /**
     * @var SearchCriteriaBuilder|MockObject
     */
    private $searchCriteriaBuilder;

    /**
     * @var FilterBuilder|MockObject
     */
    private $filterBuilder;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    private $customerGroupRepository;

    /**
     * @var WebsiteRepositoryInterface|MockObject
     */
    private $websiteRepository;

    /**
     * @var Result|MockObject
     */
    private $validationResult;

    /**
     * @var InvalidSkuProcessor|MockObject
     */
    private $invalidSkuProcessor;

    /**
     * @var TierPriceInterface|MockObject
     */
    private $tierPrice;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->productIdLocator = $this->getMockBuilder(ProductIdLocatorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->searchCriteriaBuilder = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilder = $this->getMockBuilder(FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerGroupRepository = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->websiteRepository = $this->getMockBuilder(WebsiteRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->validationResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invalidSkuProcessor = $this
            ->getMockBuilder(InvalidSkuProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tierPrice = $this->getMockBuilder(TierPriceInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManager($this);
        $this->tierPriceValidator = $objectManagerHelper->getObject(
            TierPriceValidator::class,
            [
                'productIdLocator' => $this->productIdLocator,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilder,
                'filterBuilder' => $this->filterBuilder,
                'customerGroupRepository' => $this->customerGroupRepository,
                'websiteRepository' => $this->websiteRepository,
                'validationResult' => $this->validationResult,
                'invalidSkuProcessor' => $this->invalidSkuProcessor
            ]
        );
    }

    /**
     * Prepare CustomerGroupRepository mock.
     *
     * @param array $returned
     * @return void
     */
    private function prepareCustomerGroupRepositoryMock(array $returned)
    {
        $searchCriteria = $this
            ->getMockBuilder(SearchCriteriaInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $filter = $this->getMockBuilder(AbstractSimpleObject::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->filterBuilder->expects($this->atLeastOnce())->method('setField')->willReturnSelf();
        $this->filterBuilder->expects($this->atLeastOnce())->method('setValue')->willReturnSelf();
        $this->filterBuilder->expects($this->atLeastOnce())->method('create')->willReturn($filter);
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilters')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('create')->willReturn($searchCriteria);
        $customerGroupSearchResults = $this
            ->getMockBuilder(GroupSearchResultsInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerGroupSearchResults->expects($this->once())->method('getItems')
            ->willReturn($returned['customerGroupSearchResults_getItems']);
        $this->customerGroupRepository->expects($this->atLeastOnce())->method('getList')
            ->willReturn($customerGroupSearchResults);
    }

    /**
     * Prepare retrieveValidationResult().
     *
     * @param string $sku
     * @param array $returned
     * @return void
     */
    private function prepareRetrieveValidationResultMethod($sku, array $returned)
    {
        $this->tierPrice->expects($this->atLeastOnce())->method('getSku')->willReturn($sku);
        $tierPriceValue = 104;
        $this->tierPrice->expects($this->atLeastOnce())->method('getPrice')->willReturn($tierPriceValue);
        $this->tierPrice->expects($this->atLeastOnce())->method('getPriceType')
            ->willReturn($returned['tierPrice_getPriceType']);
        $qty = 0;
        $this->tierPrice->expects($this->atLeastOnce())->method('getQuantity')->willReturn($qty);
        $websiteId = 0;
        $invalidWebsiteId = 4;
        $this->tierPrice->expects($this->atLeastOnce())->method('getWebsiteId')
            ->willReturnOnConsecutiveCalls($websiteId, $websiteId, $websiteId, $invalidWebsiteId, $websiteId);
        $this->tierPrice->expects($this->atLeastOnce())->method('getCustomerGroup')
            ->willReturn($returned['tierPrice_getCustomerGroup']);
        $skuDiff = [$sku];
        $this->invalidSkuProcessor->expects($this->atLeastOnce())->method('retrieveInvalidSkuList')
            ->willReturn($skuDiff);
        $productId = 3346346;
        $productType = Type::TYPE_BUNDLE;
        $idsBySku = [
            $sku => [$productId => $productType]
        ];
        $this->productIdLocator->expects($this->atLeastOnce())->method('retrieveProductIdsBySkus')
            ->willReturn($idsBySku);
    }

    /**
     * Test for validateSkus().
     *
     * @return void
     */
    public function testValidateSkus()
    {
        $skus = ['SDFS234234'];
        $this->invalidSkuProcessor->expects($this->atLeastOnce())
            ->method('filterSkuList')
            ->with($skus, [])
            ->willReturn($skus);

        $this->assertEquals($skus, $this->tierPriceValidator->validateSkus($skus));
    }

    /**
     * Test for retrieveValidationResult().
     *
     * @param array $returned
     * @dataProvider retrieveValidationResultDataProvider
     * @return void
     */
    public function testRetrieveValidationResult(array $returned)
    {
        $sku = 'ASDF234234';
        $prices = [$this->tierPrice];
        $existingPrices = [$this->tierPrice];
        $this->prepareRetrieveValidationResultMethod($sku, $returned);
        $website = $this->getMockBuilder(WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->websiteRepository->expects($this->atLeastOnce())->method('getById')->willReturn($website);
        $this->prepareCustomerGroupRepositoryMock($returned);

        $this->assertEquals(
            $this->validationResult,
            $this->tierPriceValidator->retrieveValidationResult($prices, $existingPrices)
        );
    }

    /**
     * Data provider for retrieveValidationResult() test.
     *
     * @return array
     */
    public function retrieveValidationResultDataProvider()
    {
        $customerGroupName = 'test_Group';
        $customerGroup = $this->getMockBuilder(GroupInterface::class)
            ->setMethods(['getCode', 'getId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $customerGroup->expects($this->atLeastOnce())->method('getCode')->willReturn($customerGroupName);
        $customerGroupId = 23;
        $customerGroup->expects($this->atLeastOnce())->method('getId')->willReturn($customerGroupId);

        return [
            [
                [
                    'tierPrice_getCustomerGroup' => $customerGroupName,
                    'tierPrice_getPriceType' => TierPriceInterface::PRICE_TYPE_DISCOUNT,
                    'customerGroupSearchResults_getItems' => [$customerGroup]
                ]
            ],
            [
                [
                    'tierPrice_getCustomerGroup' => $customerGroupName,
                    'tierPrice_getPriceType' => TierPriceInterface::PRICE_TYPE_FIXED,
                    'customerGroupSearchResults_getItems' => []
                ]
            ]
        ];
    }

    /**
     * Test for retrieveValidationResult() with Exception.
     *
     * @return void
     */
    public function testRetrieveValidationResultWithException()
    {
        $sku = 'ASDF234234';
        $customerGroupName = 'test_Group';
        $prices = [$this->tierPrice];
        $existingPrices = [$this->tierPrice];
        $returned = [
            'tierPrice_getPriceType' => TierPriceInterface::PRICE_TYPE_DISCOUNT,
            'customerGroupSearchResults_getItems' => [],
            'tierPrice_getCustomerGroup' => $customerGroupName,
        ];
        $this->prepareRetrieveValidationResultMethod($sku, $returned);
        $exception = new NoSuchEntityException();
        $this->websiteRepository->expects($this->atLeastOnce())->method('getById')->willThrowException($exception);
        $this->prepareCustomerGroupRepositoryMock($returned);

        $this->assertEquals(
            $this->validationResult,
            $this->tierPriceValidator->retrieveValidationResult($prices, $existingPrices)
        );
    }
}
