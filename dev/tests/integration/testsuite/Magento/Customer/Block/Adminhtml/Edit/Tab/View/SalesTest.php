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
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Controller\RegistryConstants;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Class SalesTest
 *
 * @magentoAppArea adminhtml
 */
class SalesTest extends \PHPUnit_Framework_TestCase
{
    const MAIN_WEBSITE = 1;

    /**
     * Sales block under test.
     *
     * @var Sales
     */
    private $block;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * Sales order view Html.
     *
     * @var string
     */
    private $html;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $objectManager->get('Magento\Framework\App\State')->setAreaCode('adminhtml');

        $this->coreRegistry = $objectManager->get('Magento\Framework\Registry');
        $this->coreRegistry->register(RegistryConstants::CURRENT_CUSTOMER_ID, 1);

        $this->block = $objectManager->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Customer\Block\Adminhtml\Edit\Tab\View\Sales',
            'sales_' . mt_rand(),
            array('coreRegistry' => $this->coreRegistry)
        )->setTemplate(
            'tab/view/sales.phtml'
        );
        $this->html = $this->block->toHtml();
    }

    /**
     * Execute post test cleanup.
     */
    public function tearDown()
    {
        $this->coreRegistry->unregister(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->html = '';
    }

    /**
     * Test basic currency formatting on the Main Website.
     */
    public function testFormatCurrency()
    {
        $this->assertEquals(
            '<span class="price">$10.00</span>',
            $this->block->formatCurrency(10.00, self::MAIN_WEBSITE)
        );
    }

    /**
     * Verify that the website is not in single store mode.
     */
    public function testIsSingleStoreMode()
    {
        $this->assertFalse($this->block->isSingleStoreMode());
    }

    /**
     * Verify sales totals. No sales so there are no totals.
     */
    public function testGetTotals()
    {
        $this->assertEquals(
            array('lifetime' => 0, 'base_lifetime' => 0, 'base_avgsale' => 0, 'num_orders' => 0),
            $this->block->getTotals()->getData()
        );
    }

    /**
     * Verify that there are no rows in the sales order grid.
     */
    public function testGetRows()
    {
        $this->assertEmpty($this->block->getRows());
    }

    /**
     * Verify that the Main Website has no websites.
     */
    public function testGetWebsiteCount()
    {
        $this->assertEquals(0, $this->block->getWebsiteCount(self::MAIN_WEBSITE));
    }

    /**
     * Verify basic content of the sales view Html.
     */
    public function testToHtml()
    {
        $this->assertContains('<span class="title">Sales Statistics</span>', $this->html);
        $this->assertContains('<strong>All Store Views</strong>', $this->html);
    }
}
