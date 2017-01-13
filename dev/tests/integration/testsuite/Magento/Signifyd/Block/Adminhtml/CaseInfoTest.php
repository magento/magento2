<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Signifyd\Block\Adminhtml;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Block\Adminhtml\Order\View\AdditionalInfo as OrderAdditionalInfo;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;
use Magento\TestFramework\Helper\Bootstrap;

class CaseInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->order = $this->objectManager->create(Order::class);
        $this->layout = $this->objectManager->get(LayoutInterface::class);
    }

    /**
     * Checks that block does not give contents
     * if Signifyd module is inactive.
     *
     * @covers \Magento\Signifyd\Block\Adminhtml\CaseInfo::isModuleActive
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 0
     * @magentoAppArea adminhtml
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
     * @magentoAppArea adminhtml
     */
    public function testCaseEntityNotExists()
    {
        $this->order->loadByIncrementId('100000001');

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
     * @magentoAppArea adminhtml
     */
    public function testCaseEntityExists()
    {
        $this->order->loadByIncrementId('100000001');

        $html = $this->getBlockContents();
        static::assertNotEmpty($html);
        static::assertContains('Some Team', $html);
        static::assertContains('col-case-score-green', $html);
    }

    /**
     * Checks that guarantee action buttons is available on order page.
     *
     * @covers \Magento\Signifyd\Block\Adminhtml\CaseInfo::getButtons
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     * @magentoAppArea adminhtml
     */
    public function testButtonsAvailable()
    {
        $this->order->loadByIncrementId('100000001');

        static::assertContains('Submit Guarantee request', $this->getBlockContents());
    }

    /**
     * Checks that guarantee action buttons is unavailable on order page.
     *
     * @covers \Magento\Signifyd\Block\Adminhtml\CaseInfo::getButtons
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     * @magentoAppArea adminhtml
     */
    public function testButtonsUnavailable()
    {
        $this->order->loadByIncrementId('100000001');
        $this->order->setState(Order::STATE_CANCELED);

        /** @var OrderRepository $orderRepository */
        $orderRepository = $this->objectManager->get(OrderRepository::class);
        $orderRepository->save($this->order);

        static::assertNotContains('Submit Guarantee request', $this->getBlockContents());
    }

    /**
     * Renders block contents.
     *
     * @return string
     */
    private function getBlockContents()
    {
        /** @var CaseInfo $block */
        $block = $this->layout->createBlock(CaseInfo::class, 'order_case_info', ['context' => $this->getContext()]);
        $block->setTemplate('Magento_Signifyd::case_info.phtml');

        /** @var OrderAdditionalInfo $parent */
        $parent = $this->layout->createBlock(OrderAdditionalInfo::class, 'order_additional_info');
        $parent->setChild('order_case_info', $block);

        return $block->toHtml();
    }

    /**
     * Creates template context with necessary order id param.
     *
     * @return Context
     */
    private function getContext()
    {
        /** @var RequestInterface $request */
        $request = $this->objectManager->get(RequestInterface::class);
        $request->setParams(['order_id' => $this->order->getEntityId()]);

        return $this->objectManager->create(Context::class, ['request' => $request]);
    }
}
