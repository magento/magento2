<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Helper;

class DataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tax helper
     *
     * @var \Magento\Tax\Helper\Data
     */
    private $helper;

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $helper \Magento\Tax\Helper\Data */
        $this->helper = $this->objectManager->get('Magento\Tax\Helper\Data');
    }

    /**
     * @magentoConfigFixture default_store tax/classes/default_customer_tax_class 1
     */
    public function testGetDefaultCustomerTaxClass()
    {
        $this->assertEquals(1, $this->helper->getDefaultCustomerTaxClass());
    }

    /**
     * @magentoConfigFixture default_store tax/classes/default_product_tax_class 1
     */
    public function testGetDefaultProductTaxClass()
    {
        $this->assertEquals(1, $this->helper->getDefaultProductTaxClass());
    }
}
