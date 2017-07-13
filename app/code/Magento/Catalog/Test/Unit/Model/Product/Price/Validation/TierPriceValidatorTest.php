<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Model\Product\Price\Validation;

/**
 * Test for \Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TierPriceValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator
     */
    private $tierPriceValidator;

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
     * @var \Magento\Catalog\Model\Product\Price\Validation\Result|\PHPUnit_Framework_MockObject_MockObject
     */
    private $validationResult;

    /**
     * @var \Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor
     *      |\PHPUnit_Framework_MockObject_MockObject
     */
    private $invalidSkuProcessor;

    /**
     * @var \Magento\Catalog\Api\Data\TierPriceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $tierPrice;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->productIdLocator = $this->getMockBuilder(\Magento\Catalog\Model\ProductIdLocatorInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->searchCriteriaBuilder = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()->getMock();
        $this->filterBuilder = $this->getMockBuilder(\Magento\Framework\Api\FilterBuilder::class)
            ->disableOriginalConstructor()->getMock();
        $this->customerGroupRepository = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->websiteRepository = $this->getMockBuilder(\Magento\Store\Api\WebsiteRepositoryInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->validationResult = $this->getMockBuilder(\Magento\Catalog\Model\Product\Price\Validation\Result::class)
            ->disableOriginalConstructor()->getMock();
        $this->invalidSkuProcessor = $this
            ->getMockBuilder(\Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor::class)
            ->disableOriginalConstructor()->getMock();
        $this->tierPrice = $this->getMockBuilder(\Magento\Catalog\Api\Data\TierPriceInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->tierPriceValidator = $objectManagerHelper->getObject(
            \Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator::class,
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
            ->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteriaInterface::class)
            ->disableOriginalConstructor()->getMock();
        $filter = $this->getMockBuilder(\Magento\Framework\Api\AbstractSimpleObject::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $this->filterBuilder->expects($this->atLeastOnce())->method('setField')->willReturnSelf();
        $this->filterBuilder->expects($this->atLeastOnce())->method('setValue')->willReturnSelf();
        $this->filterBuilder->expects($this->atLeastOnce())->method('create')->willReturn($filter);
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('addFilters')->willReturnSelf();
        $this->searchCriteriaBuilder->expects($this->atLeastOnce())->method('create')->willReturn($searchCriteria);
        $customerGroupSearchResults = $this
            ->getMockBuilder(\Magento\Customer\Api\Data\GroupSearchResultsInterface::class)
            ->disableOriginalConstructor()->getMock();
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
        $productType = \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE;
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
        $website = $this->getMockBuilder(\Magento\Store\Api\Data\WebsiteInterface::class)
            ->disableOriginalConstructor()->getMockForAbstractClass();
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
        $customerGroup = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->setMethods(['getCode', 'getId'])
            ->disableOriginalConstructor()->getMockForAbstractClass();
        $customerGroup->expects($this->atLeastOnce())->method('getCode')->willReturn($customerGroupName);
        $customerGroupId = 23;
        $customerGroup->expects($this->atLeastOnce())->method('getId')->willReturn($customerGroupId);

        return [
            [
                [
                    'tierPrice_getCustomerGroup' => $customerGroupName,
                    'tierPrice_getPriceType' => \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_DISCOUNT,
                    'customerGroupSearchResults_getItems' => [$customerGroup]
                ]
            ],
            [
                [
                    'tierPrice_getCustomerGroup' => $customerGroupName,
                    'tierPrice_getPriceType' => \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_FIXED,
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
            'tierPrice_getPriceType' => \Magento\Catalog\Api\Data\TierPriceInterface::PRICE_TYPE_DISCOUNT,
            'customerGroupSearchResults_getItems' => [],
            'tierPrice_getCustomerGroup' => $customerGroupName,
        ];
        $this->prepareRetrieveValidationResultMethod($sku, $returned);
        $exception = new \Magento\Framework\Exception\NoSuchEntityException();
        $this->websiteRepository->expects($this->atLeastOnce())->method('getById')->willThrowException($exception);
        $this->prepareCustomerGroupRepositoryMock($returned);

        $this->assertEquals(
            $this->validationResult,
            $this->tierPriceValidator->retrieveValidationResult($prices, $existingPrices)
        );
    }
}
