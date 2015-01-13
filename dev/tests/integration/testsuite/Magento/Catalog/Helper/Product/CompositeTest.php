<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Helper\Product;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test Composite
 */
class CompositeTest extends \PHPUnit_Framework_TestCase
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
        $this->helper = Bootstrap::getObjectManager()->get('Magento\Catalog\Helper\Product\Composite');
        $this->registry = Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
    }

    protected function tearDown()
    {
        $this->registry->unregister('composite_configure_result_error_message');
        $this->registry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->registry->unregister(RegistryConstants::CURRENT_CUSTOMER);
        $this->registry->unregister('current_product');
        $this->registry->unregister('product');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testRenderConfigureResult()
    {
        $configureResult = new \Magento\Framework\Object();
        $configureResult->setOk(true)
            ->setProductId(1)
            ->setCurrentCustomerId(1);

        $this->helper->renderConfigureResult($configureResult);

        $customerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->assertEquals(1, $customerId);
        $errorMessage = $this->registry->registry('composite_configure_result_error_message');
        $this->assertNull($errorMessage);
    }

    public function testRenderConfigureResultNotOK()
    {
        $configureResult = new \Magento\Framework\Object();
        $configureResult->setError(true)
            ->setMessage('Test Message');

        $this->helper->renderConfigureResult($configureResult);

        $customerId = $this->registry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->assertNull($customerId);
        $errorMessage = $this->registry->registry('composite_configure_result_error_message');
        $this->assertEquals('Test Message', $errorMessage);
    }
}
