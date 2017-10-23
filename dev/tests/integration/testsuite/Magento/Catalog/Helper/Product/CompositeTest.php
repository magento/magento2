<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Helper\Product;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test Composite
 */
class CompositeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Composite
     */
    protected $helper;

    /**
     * @var Registry
     */
    protected $registry;

    protected function setUp()
    {
        $this->helper = Bootstrap::getObjectManager()->get(\Magento\Catalog\Helper\Product\Composite::class);
        $this->registry = Bootstrap::getObjectManager()->get(\Magento\Framework\Registry::class);
    }

    protected function tearDown()
    {
        $this->registry->unregister('composite_configure_result_error_message');
        $this->registry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->registry->unregister('current_product');
        $this->registry->unregister('product');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testRenderConfigureResult()
    {
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Api\ProductRepositoryInterface::class
        );
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $productRepository->get('simple');

        $configureResult = new \Magento\Framework\DataObject();
        $configureResult->setOk(true)
            ->setProductId($product->getId())
            ->setCurrentCustomerId(1);

        $this->helper->renderConfigureResult($configureResult);

        $customerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->assertEquals(1, $customerId);
        $errorMessage = $this->registry->registry('composite_configure_result_error_message');
        $this->assertNull($errorMessage);
    }

    public function testRenderConfigureResultNotOK()
    {
        $configureResult = new \Magento\Framework\DataObject();
        $configureResult->setError(true)
            ->setMessage('Test Message');

        $this->helper->renderConfigureResult($configureResult);

        $customerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->assertNull($customerId);
        $errorMessage = $this->registry->registry('composite_configure_result_error_message');
        $this->assertEquals('Test Message', $errorMessage);
    }
}
