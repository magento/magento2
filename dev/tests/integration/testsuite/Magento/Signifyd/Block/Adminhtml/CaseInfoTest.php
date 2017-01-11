<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Signifyd\Block\Adminhtml;

use Magento\Framework\App\Area;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View\Tab\Info as OrderTabInfo;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;

class CaseInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $bootstrap = Bootstrap::getInstance();
        $bootstrap->loadArea(Area::AREA_ADMINHTML);

        $objectManager = Bootstrap::getObjectManager();
        $this->order = $objectManager->create(Order::class);
        $this->registry = $objectManager->get(Registry::class);
        $this->layout = $objectManager->get(LayoutInterface::class);
    }

    /**
     * Checks that block does not give contents
     * if Signifyd module is inactive.
     *
     * @covers \Magento\Signifyd\Block\Adminhtml\CaseInfo::isModuleActive
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 0
     */
    public function testModuleIsInactive()
    {
        static::assertEmpty($this->getBlockContents());
    }

    /**
     * Checks that block does not give contents
     * if there is no case entity created for order.
     *
     * @covers \Magento\Signifyd\Block\Adminhtml\CaseInfo::getCaseEntity
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     * @magentoDataFixture Magento/Signifyd/_files/order_with_customer_and_two_simple_products.php
     */
    public function testCaseEntityNotExists()
    {
        $this->registry->register('current_order', $this->order->loadByIncrementId('100000001'));

        static::assertEmpty($this->getBlockContents());
    }

    /**
     * Checks that:
     * - block give contents
     * - associated team displays correct
     * - score class displays correct
     *
     * @covers \Magento\Signifyd\Block\Adminhtml\CaseInfo::getAssociatedTeam
     * @covers \Magento\Signifyd\Block\Adminhtml\CaseInfo::getScoreClass
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     */
    public function testCaseEntityExists()
    {
        $this->registry->register('current_order', $this->order->loadByIncrementId('100000001'));

        $html = $this->getBlockContents();
        static::assertNotEmpty($html);
        static::assertContains('Some Team', $html);
        static::assertContains('col-case-score-green', $html);
    }

    /**
     * Renders block contents.
     *
     * @return string
     */
    private function getBlockContents()
    {
        /** @var CaseInfo $block */
        $block = $this->layout->createBlock(CaseInfo::class, 'order_case_info');
        $block->setTemplate('Magento_Signifyd::case_info.phtml');

        /** @var OrderTabInfo $parent */
        $parent = $this->layout->createBlock(OrderTabInfo::class, 'order_tab_info');
        $parent->setChild('order_case_info', $block);

        return $block->toHtml();
    }
}
