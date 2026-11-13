<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * A "from" date later than its "to" date is rejected on every save path, not only when an Adminhtml controller
 * armed the attribute's max value.
 */
#[
    DataFixture(ProductFixture::class, as: 'product'),
]
class StartdateWindowTest extends TestCase
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

    #[DataProvider('invertedWindowProvider')]
    public function testInvertedProductWindowIsRejected(string $from, string $to): void
    {
        $product = $this->loadProduct();
        $product->setData($from, '2027-06-01')->setData($to, '2026-06-01');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Make sure the To Date is later than or the same as the From Date.');
        $this->productRepository->save($product);
    }

    public static function invertedWindowProvider(): array
    {
        return [
            'special price' => ['special_from_date', 'special_to_date'],
            'new from' => ['news_from_date', 'news_to_date'],
            'custom design' => ['custom_design_from', 'custom_design_to'],
        ];
    }

    public function testInvertedWindowAgainstStoredToDateIsRejected(): void
    {
        $product = $this->loadProduct();
        $product->setData('special_to_date', '2028-06-01');
        $this->productRepository->save($product);

        $product = $this->loadProduct();
        $product->setData('special_from_date', '2029-06-01');

        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Make sure the To Date is later than or the same as the From Date.');
        $this->productRepository->save($product);
    }

    public function testUnrelatedSaveOfProductWithStoredInvertedWindowIsAllowed(): void
    {
        $product = $this->loadProduct();
        $product->setData('special_from_date', '2027-06-01')->setData('special_to_date', '2026-06-01');
        $resource = Bootstrap::getObjectManager()->get(ProductResource::class);
        $resource->saveAttribute($product, 'special_from_date');
        $resource->saveAttribute($product, 'special_to_date');

        $product = $this->loadProduct();
        $product->setName('Renamed by an unrelated update');
        $this->productRepository->save($product);

        $this->assertSame('Renamed by an unrelated update', $this->loadProduct()->getName());
    }

    public function testChangingTheFromDateOfAStoredInvertedWindowIsStillRejected(): void
    {
        $product = $this->loadProduct();
        $product->setData('special_to_date', '2026-06-01');
        $resource = Bootstrap::getObjectManager()->get(ProductResource::class);
        $resource->saveAttribute($product, 'special_to_date');

        $product = $this->loadProduct();
        $product->setData('special_from_date', '2027-01-01');

        $this->expectException(LocalizedException::class);
        $this->productRepository->save($product);
    }

    public function testFullPayloadResendOfStoredInvertedWindowIsAllowed(): void
    {
        $product = $this->loadProduct();
        $product->setData('special_from_date', '2027-06-01')->setData('special_to_date', '2026-06-01');
        $resource = Bootstrap::getObjectManager()->get(ProductResource::class);
        $resource->saveAttribute($product, 'special_from_date');
        $resource->saveAttribute($product, 'special_to_date');

        $product = $this->loadProduct();
        $product->setData('special_from_date', '2027-06-01')
            ->setData('special_to_date', '2026-06-01')
            ->setName('Renamed by a full-payload update');
        $this->productRepository->save($product);

        $this->assertSame('Renamed by a full-payload update', $this->loadProduct()->getName());
    }

    #[DataProvider('storedAsInvertedProvider')]
    public function testWindowThatWouldBeStoredInvertedIsRejected($from, $to): void
    {
        $product = $this->loadProduct();
        $product->setData('special_from_date', $from)->setData('special_to_date', $to);

        $this->expectException(LocalizedException::class);
        $this->productRepository->save($product);
    }

    public static function storedAsInvertedProvider(): array
    {
        return [
            'digits are a timestamp when stored' => ['2027-06-01', '20270701'],
            'integer timestamps' => [1814400000, 1779926400],
        ];
    }

    #[DataProvider('sameDayOrEmptyEndProvider')]
    public function testSameDayOrEmptyEndIsSaved(string $from, string $to): void
    {
        $product = $this->loadProduct();
        $product->setData('special_from_date', $from)->setData('special_to_date', $to);

        $this->productRepository->save($product);

        $this->assertNotNull($this->loadProduct()->getData('special_from_date'));
    }

    public static function sameDayOrEmptyEndProvider(): array
    {
        return [
            'from with time, same day' => ['2027-06-01 10:00:00', '2027-06-01'],
            'to with offset, same day' => ['2027-06-01', '2027-06-01T00:00:00+02:00'],
            'from ISO with T, same day' => ['2027-06-01T08:30:00', '2027-06-01'],
            'zero to date' => ['2026-06-01', '0000-00-00 00:00:00'],
        ];
    }

    #[DataProvider('validWindowProvider')]
    public function testValidOrOpenWindowIsSaved(?string $from, ?string $to): void
    {
        $product = $this->loadProduct();
        $product->setData('special_from_date', $from)->setData('special_to_date', $to);

        $this->productRepository->save($product);

        $saved = $this->loadProduct();
        $this->assertSame($from === null ? null : $from . ' 00:00:00', $saved->getData('special_from_date'));
    }

    public static function validWindowProvider(): array
    {
        return [
            'from before to' => ['2026-06-01', '2027-06-01'],
            'same day' => ['2026-06-01', '2026-06-01'],
            'only from' => ['2027-06-01', null],
            'only to' => [null, '2026-06-01'],
        ];
    }

    #[DataFixture(CategoryFixture::class, as: 'category')]
    public function testInvertedCategoryDesignWindowIsRejected(): void
    {
        $categoryRepository = Bootstrap::getObjectManager()->get(CategoryRepositoryInterface::class);
        $category = $categoryRepository->get($this->fixtures->get('category')->getId(), 0);
        $category->setCustomAttribute('custom_design_from', '2027-06-01');
        $category->setCustomAttribute('custom_design_to', '2026-06-01');

        $this->expectException(CouldNotSaveException::class);
        $categoryRepository->save($category);
    }

    private function loadProduct()
    {
        return $this->productRepository->get($this->fixtures->get('product')->getSku(), true, 0, true);
    }
}
