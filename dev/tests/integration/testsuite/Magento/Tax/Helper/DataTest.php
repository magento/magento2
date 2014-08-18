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
     * @var \Magento\Framework\ObjectManager
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
