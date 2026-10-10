<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Review\Block;

use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\ReinitableConfig;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\ButtonLockManager;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for {@see Form}.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class FormTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private ObjectManager $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        parent::setUp();
    }

    /**
     * Verify that getAllowWriteReviewFlag returns the correct value based on configuration
     *
     * @param string $path
     * @param string $scope
     * @param string $scopeId
     * @param int $value
     * @param bool $expectedResult
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Review/_files/config.php
     */
    #[DataProvider('getCorrectFlagDataProvider')]
    public function testGetCorrectFlag(
        $path,
        $scope,
        $scopeId,
        $value,
        $expectedResult
    ): void {
        /** @var State $appState */
        $appState = $this->objectManager->get(State::class);
        $appState->setAreaCode(Area::AREA_FRONTEND);

        /** @var Value $config */
        $config = $this->objectManager->create(Value::class);
        $config->setPath($path);
        $config->setScope($scope);
        $config->setScopeId($scopeId);
        $config->setValue($value);
        $config->save();
        /** @var ReinitableConfig $reinitableConfig */
        $reinitableConfig = $this->objectManager->create(ReinitableConfig::class);
        $reinitableConfig->reinit();

        /** @var Form $form */
        $form = $this->objectManager->create(Form::class);
        $form->setButtonLockManager(
            $this->objectManager->create(ButtonLockManager::class, ['buttonLockPool' => []])
        );
        $result = $form->getAllowWriteReviewFlag();
        $this->assertSame($expectedResult, $result);
    }

    /**
     * Data provider for testGetCorrectFlag
     *
     * @return array
     */
    public static function getCorrectFlagDataProvider(): array
    {
        return [
            'Guest review disabled' => [
                'path' => 'catalog/review/allow_guest',
                'scope' => 'websites',
                'scopeId' => '1',
                'value' => 0,
                'expectedResult' => false,
            ],
            'Guest review enabled' => [
                'path' => 'catalog/review/allow_guest',
                'scope' => 'websites',
                'scopeId' => '1',
                'value' => 1,
                'expectedResult' => true,
            ],
        ];
    }

    /**
     * Verify that getProductInfo returns correct product data for a valid numeric ID
     */
    #[
        AppArea('frontend'),
        DataFixture(ProductFixture::class, as: 'product'),
    ]
    public function testGetProductInfoWithValidId(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $product = $fixtures->get('product');

        $form = $this->objectManager->create(Form::class);
        $form->getRequest()
            ->setMethod(Http::METHOD_GET)
            ->setParams(['id' => $product->getId()]);

        $productInfo = $form->getProductInfo();
        $this->assertSame((int)$product->getId(), (int)$productInfo->getId());
        $this->assertSame($product->getSku(), $productInfo->getSku());
    }

    /**
     * Verify that getProductInfo loads the product when the request id has a numeric prefix
     * followed by non-numeric characters (e.g. "123abc"): Form::getProductId() casts to int,
     * so the leading digits are used as the entity id.
     */
    #[
        AppArea('frontend'),
        DataFixture(ProductFixture::class, as: 'product'),
    ]
    public function testGetProductInfoWithTrailingAlphaInId(): void
    {
        $fixtures = DataFixtureStorageManager::getStorage();
        $product = $fixtures->get('product');

        $form = $this->objectManager->create(Form::class);
        $form->getRequest()
            ->setMethod(Http::METHOD_GET)
            ->setParams(['id' => $product->getId() . 'abc']);

        $productInfo = $form->getProductInfo();
        $this->assertSame((int)$product->getId(), (int)$productInfo->getId());
        $this->assertSame($product->getSku(), $productInfo->getSku());
    }

    /**
     * Verify that getProductInfo throws NoSuchEntityException for a purely non-numeric ID
     */
    #[
        AppArea('frontend'),
        DataFixture(ProductFixture::class, as: 'product'),
    ]
    public function testGetProductInfoWithNonNumericId(): void
    {
        $form = $this->objectManager->create(Form::class);
        $form->getRequest()
            ->setMethod(Http::METHOD_GET)
            ->setParams(['id' => 'abc']);

        $this->expectException(NoSuchEntityException::class);
        $form->getProductInfo();
    }

    /**
     * Verify that getProductInfo throws NoSuchEntityException when product ID is empty
     */
    #[
        AppArea('frontend'),
    ]
    public function testGetProductInfoWithEmptyId(): void
    {
        $form = $this->objectManager->create(Form::class);
        $form->getRequest()
            ->setMethod(Http::METHOD_GET)
            ->setParams(['id' => '']);

        $this->expectException(NoSuchEntityException::class);
        $form->getProductInfo();
    }
}
