<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
