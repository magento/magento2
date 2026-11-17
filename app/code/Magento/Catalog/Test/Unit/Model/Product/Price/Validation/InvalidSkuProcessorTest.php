<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Price\Validation;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Price\Validation\InvalidSkuProcessor;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ProductIdLocatorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for model Magento\Catalog\Product\Price\Validation\InvalidSkuProcessor.
 */
class InvalidSkuProcessorTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var InvalidSkuProcessor
     */
    private $invalidSkuProcessor;

    /**
     * @var ProductIdLocatorInterface|MockObject
     */
    private $productIdLocator;

    /**
     * @var ProductRepositoryInterface|MockObject
     */
    private $productRepository;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->productIdLocator = $this->createMock(ProductIdLocatorInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);

        $objectManager = new ObjectManager($this);
        $this->invalidSkuProcessor = $objectManager->getObject(
            InvalidSkuProcessor::class,
            [
                'productIdLocator' => $this->productIdLocator,
                'productRepository' => $this->productRepository
            ]
        );
    }

    /**
     * Prepare retrieveInvalidSkuList().
     *
     * @param string $productType
     * @param string $productSku
     * @return void
     */
    private function prepareRetrieveInvalidSkuListMethod($productType, $productSku)
    {
        $idsBySku = [$productSku => [235235235 => $productType]];
        $this->productIdLocator->expects($this->atLeastOnce())->method('retrieveProductIdsBySkus')
            ->willReturn($idsBySku);
        /** @var ProductInterface $product */
        $product = $this->createPartialMockWithReflection(
            Product::class,
            ['getPriceType']
        );
        $product->method('getPriceType')->willReturn(0);
        $this->productRepository->expects($this->atLeastOnce())->method('get')->willReturn($product);
    }

    /**
     * Test for retrieveInvalidSkuList().
     *
     * @return void
     */
    public function testRetrieveInvalidSkuList()
    {
        $productSku = 'LKJKJ2233636';
        $productType = Type::TYPE_BUNDLE;
        $methodParamSku = 'SDFSDF3242355';
        $skus = [$methodParamSku];
        $allowedProductTypes = [$productType];
        $allowedPriceTypeValue = true;
        $this->prepareRetrieveInvalidSkuListMethod($productType, $productSku);

        $this->assertEquals(
            [$methodParamSku, $productSku],
            $this->invalidSkuProcessor->retrieveInvalidSkuList($skus, $allowedProductTypes, $allowedPriceTypeValue)
        );
    }

    /**
     * A bundle product can be deleted between the retrieveProductIdsBySkus() lookup and the
     * productRepository->get() call a few lines later. Before this fix, the resulting
     * NoSuchEntityException propagated uncaught and failed the whole batch instead of just
     * this one SKU.
     *
     * @return void
     */
    public function testRetrieveInvalidSkuListWhenBundleProductDeletedConcurrently()
    {
        $deletedSku = 'DELETED-BUNDLE-SKU';
        $idsBySku = [$deletedSku => [235235235 => Type::TYPE_BUNDLE]];
        $this->productIdLocator->expects($this->atLeastOnce())->method('retrieveProductIdsBySkus')
            ->willReturn($idsBySku);
        $this->productRepository->expects($this->atLeastOnce())->method('get')
            ->willThrowException(new NoSuchEntityException());

        // array_values(): retrieveInvalidSkuList() never guarantees zero-based
        // keys (array_udiff()'s internal next-index counter isn't reset just
        // because the filtered result is empty) and no caller relies on the
        // keys, only the values -- same as filterSkuList()'s own array_diff().
        $this->assertEquals(
            [$deletedSku],
            array_values($this->invalidSkuProcessor->retrieveInvalidSkuList(
                [$deletedSku],
                [Type::TYPE_BUNDLE],
                true
            ))
        );
    }

    /**
     * One bundle product being deleted mid-batch must not stop the rest of the batch from
     * being processed normally.
     *
     * @return void
     */
    public function testRetrieveInvalidSkuListContinuesBatchWhenOneBundleProductIsDeletedConcurrently()
    {
        $deletedSku = 'DELETED-BUNDLE-SKU';
        $validSku = 'VALID-BUNDLE-SKU';
        $idsBySku = [
            $deletedSku => [235235235 => Type::TYPE_BUNDLE],
            $validSku => [235235236 => Type::TYPE_BUNDLE],
        ];
        $this->productIdLocator->expects($this->atLeastOnce())->method('retrieveProductIdsBySkus')
            ->willReturn($idsBySku);

        $validProduct = $this->createPartialMockWithReflection(Product::class, ['getPriceType']);
        $validProduct->method('getPriceType')->willReturn(true);

        $this->productRepository->method('get')
            ->willReturnCallback(function ($sku) use ($deletedSku, $validProduct) {
                if ($sku === $deletedSku) {
                    throw new NoSuchEntityException();
                }
                return $validProduct;
            });

        $this->assertEquals(
            [$deletedSku],
            array_values($this->invalidSkuProcessor->retrieveInvalidSkuList(
                [$deletedSku, $validSku],
                [Type::TYPE_BUNDLE],
                true
            ))
        );
    }
}
