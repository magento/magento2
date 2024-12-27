<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Price\Validation;

use Magento\Catalog\Api\Data\TierPriceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor;
use Magento\Catalog\Model\Product\Price\Validation\Result;
use Magento\Catalog\Model\Product\Price\Validation\TierPriceValidator;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
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
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepository;

    /**
     * @var ResourceConnection|MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var AdapterInterface|MockObject
     */
    private $adapterInterface;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->productIdLocator = $this->getMockBuilder(ProductIdLocatorInterface::class)
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
        $this->productRepository = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapterInterface = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $objectManagerHelper = new ObjectManager($this);
        $this->tierPriceValidator = $objectManagerHelper->getObject(
            TierPriceValidator::class,
            [
                'productIdLocator' => $this->productIdLocator,
                'websiteRepository' => $this->websiteRepository,
                'validationResult' => $this->validationResult,
                'invalidSkuProcessor' => $this->invalidSkuProcessor,
                'productRepository' => $this->productRepository,
                'resourceConnection' => $this->resourceConnectionMock
            ]
        );
    }

    /**
     * Prepare CustomerGroupRepository mock.
     *
     * @return void
     */
    private function prepareCustomerGroupRepositoryMock()
    {
        $select = $this->createMock(Select::class);
        $select->expects($this->once())
            ->method('from')
            ->with('customer_group', 'customer_group_id')
            ->willReturnSelf();
        $select->expects($this->once())
            ->method('where')
            ->with('customer_group_code = ?', 'test_group')
            ->willReturnSelf();
        $this->adapterInterface->expects($this->once())
            ->method('select')
            ->willReturn($select);

        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->willReturn($this->adapterInterface);
        $this->resourceConnectionMock->expects($this->once())
            ->method('getTableName')
            ->willReturnArgument(0);
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
            ->willReturnCallback(function () use (&$callCount, $websiteId, $invalidWebsiteId) {
                $callCount++;
                if ($callCount === 4) {
                    return $invalidWebsiteId;
                }
                return $websiteId;
            });
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

        $product = $this->getMockBuilder(\Magento\Catalog\Model\Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $type = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->onlyMethods(['canUseQtyDecimals'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->productRepository->expects($this->once())
            ->method('get')
            ->with($sku)
            ->willReturn($product);

        $product->expects($this->once())
            ->method('getTypeInstance')
            ->willReturn($type);

        $type->expects($this->once())
            ->method('canUseQtyDecimals')
            ->willReturn(true);
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
        $this->prepareCustomerGroupRepositoryMock();

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
    public static function retrieveValidationResultDataProvider()
    {
        $customerGroupName = 'test_Group';
        return [
            [
                [
                    'tierPrice_getCustomerGroup' => $customerGroupName,
                    'tierPrice_getPriceType' => TierPriceInterface::PRICE_TYPE_DISCOUNT
                ]
            ],
            [
                [
                    'tierPrice_getCustomerGroup' => $customerGroupName,
                    'tierPrice_getPriceType' => TierPriceInterface::PRICE_TYPE_FIXED
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
            'tierPrice_getCustomerGroup' => $customerGroupName,
        ];
        $this->prepareRetrieveValidationResultMethod($sku, $returned);
        $exception = new NoSuchEntityException();
        $this->websiteRepository->expects($this->atLeastOnce())->method('getById')->willThrowException($exception);
        $this->prepareCustomerGroupRepositoryMock();

        $this->assertEquals(
            $this->validationResult,
            $this->tierPriceValidator->retrieveValidationResult($prices, $existingPrices)
        );
    }
}
