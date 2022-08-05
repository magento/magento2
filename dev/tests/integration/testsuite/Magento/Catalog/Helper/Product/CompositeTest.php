<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Helper\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\DataObject;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test Composite
 */
class CompositeTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Composite */
    private $helper;

    /** @var Registry */
    private $registry;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->helper = $this->objectManager->get(Composite::class);
        $this->registry = $this->objectManager->get(Registry::class);
        $this->productRepository = $this->objectManager->get(ProductRepositoryInterface::class);
        $this->productRepository->cleanCache();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('composite_configure_result_error_message');
        $this->registry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->registry->unregister('current_product');
        $this->registry->unregister('product');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @return void
     */
    public function testRenderConfigureResult(): void
    {
        $product = $this->productRepository->get('simple');
        /** @var DataObject $buyRequest */
        $buyRequest = $this->objectManager->create(DataObject::class);
        $buyRequest->setData(['qty' => 1]);
        /** @var DataObject $configureResult */
        $configureResult = $this->objectManager->create(DataObject::class);
        $configureResult->setOk(true)
            ->setProductId($product->getId())
            ->setBuyRequest($buyRequest)
            ->setCurrentCustomerId(1);

        $resultLayout = $this->helper->renderConfigureResult($configureResult);

        /** @var Product $preparedProduct */
        $preparedProduct = $this->registry->registry('product');
        $preparedCurrentProduct = $this->registry->registry('current_product');
        $this->assertTrue($preparedProduct && $preparedCurrentProduct);
        $this->assertEquals(1, $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID));
        $this->assertNotNull($preparedProduct->getPreconfiguredValues());
        $this->assertContains(
            'CATALOG_PRODUCT_COMPOSITE_CONFIGURE',
            $resultLayout->getLayout()->getUpdate()->getHandles()
        );
        $this->assertContains(
            'catalog_product_view_type_' . $product->getTypeId(),
            $resultLayout->getLayout()->getUpdate()->getHandles()
        );
    }

    /**
     * @dataProvider renderConfigureResultExceptionProvider
     * @param array $data
     * @param string $expectedErrorMessage
     * @return void
     */
    public function testRenderConfigureResultException(array $data, string $expectedErrorMessage): void
    {
        /** @var DataObject $configureResult */
        $configureResult = $this->objectManager->create(DataObject::class);
        $configureResult->setData($data);

        $resultLayout = $this->helper->renderConfigureResult($configureResult);

        $this->assertEquals(
            $expectedErrorMessage,
            $this->registry->registry('composite_configure_result_error_message')
        );
        $this->assertContains(
            'CATALOG_PRODUCT_COMPOSITE_CONFIGURE_ERROR',
            $resultLayout->getLayout()->getUpdate()->getHandles()
        );
    }

    /**
     * Create render configure result exception provider
     *
     * @return array
     */
    public function renderConfigureResultExceptionProvider(): array
    {
        return [
            'error_true' => [
                'data' => [
                    'error' => true,
                    'message' => 'Test Message'
                ],
                'expected_error_message' => 'Test Message',
            ],
            'without_product' => [
                'data' => [
                    'ok' => true,
                ],
                'expected_error_message' => 'The product that was requested doesn\'t exist.'
                    . ' Verify the product and try again.',
            ],
        ];
    }
}
