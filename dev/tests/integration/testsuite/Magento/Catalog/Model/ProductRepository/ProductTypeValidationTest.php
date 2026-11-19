<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ProductRepository;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * A product type that is not declared in product_types.xml must be rejected on save, not stored and later treated as
 * "simple" at runtime.
 */
class ProductTypeValidationTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp(): void
    {
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
    }

    #[DataFixture(ProductFixture::class, as: 'product')]
    public function testUpdateWithUnknownTypeIsRejected(): void
    {
        $sku = $this->fixtures->get('product')->getSku();
        $product = $this->productRepository->get($sku, true, 0, true);
        $product->setTypeId('nonsense');

        try {
            $this->productRepository->save($product);
            $this->fail('A product with an unknown type was saved');
        } catch (CouldNotSaveException $exception) {
            $this->assertStringContainsString('nonsense', $exception->getMessage());
        }
        $this->assertSame('simple', $this->storedTypeId($sku));
    }

    public function testCreateWithUnknownTypeIsRejected(): void
    {
        $product = Bootstrap::getObjectManager()->get(ProductInterfaceFactory::class)->create();
        $product->setSku('unknown-type-create')
            ->setName('Unknown type')
            ->setAttributeSetId(4)
            ->setPrice(10)
            ->setTypeId('virtual2');

        try {
            $this->productRepository->save($product);
            $this->fail('A product with an unknown type was created');
        } catch (CouldNotSaveException $exception) {
            $this->assertStringContainsString('virtual2', $exception->getMessage());
        }
        $this->assertNull($this->storedTypeId('unknown-type-create'));
    }

    #[DataFixture(ProductFixture::class, as: 'product')]
    public function testUpdateToAnotherDeclaredTypeIsStillAllowed(): void
    {
        $sku = $this->fixtures->get('product')->getSku();
        $product = $this->productRepository->get($sku, true, 0, true);
        $product->setTypeId('virtual');

        $this->productRepository->save($product);

        $this->assertSame('virtual', $this->storedTypeId($sku));
    }

    #[DataFixture(ProductFixture::class, as: 'product')]
    public function testUpdateWithoutTypeKeepsTheType(): void
    {
        $sku = $this->fixtures->get('product')->getSku();
        $product = $this->productRepository->get($sku, true, 0, true);
        $product->setName('Renamed');

        $this->productRepository->save($product);

        $this->assertSame('simple', $this->storedTypeId($sku));
    }

    #[DataFixture(ProductFixture::class, as: 'product')]
    public function testProductWithAStoredUndeclaredTypeStaysEditable(): void
    {
        $sku = $this->fixtures->get('product')->getSku();
        $connection = Bootstrap::getObjectManager()->get(ResourceConnection::class)->getConnection();
        $connection->update(
            $connection->getTableName('catalog_product_entity'),
            ['type_id' => 'module_disabled_type'],
            ['sku = ?' => $sku]
        );
        $this->productRepository->cleanCache();

        $product = $this->productRepository->get($sku, true, 0, true);
        $product->setName('Edited after its type module was disabled');
        $this->productRepository->save($product);

        $this->assertSame('module_disabled_type', $this->storedTypeId($sku));
    }

    private function storedTypeId(string $sku): ?string
    {
        $connection = Bootstrap::getObjectManager()->get(ResourceConnection::class)->getConnection();
        $typeId = $connection->fetchOne(
            $connection->select()
                ->from($connection->getTableName('catalog_product_entity'), ['type_id'])
                ->where('sku = ?', $sku)
        );
        return $typeId === false ? null : $typeId;
    }
}
