<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Block\Adminhtml;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;

class CaseInfoTest extends \PHPUnit\Framework\TestCase
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
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->order = $this->objectManager->create(Order::class);
        $this->layoutFactory = $this->objectManager->get(\Magento\Framework\View\LayoutFactory::class);
    }

    /**
     * Checks that block has contents when case entity for order is exists
     * even if Signifyd module is inactive.
     *
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 0
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     * @magentoAppArea adminhtml
     */
    public function testModuleIsInactive()
    {
        $this->order->loadByIncrementId('100000001');

        self::assertNotEmpty($this->getBlock()->toHtml());
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

        self::assertEmpty($this->getBlock()->toHtml());
    }

    /**
     * Checks that:
     * - block give contents
     * - block contents guarantee decision field
     *
     * @covers \Magento\Signifyd\Block\Adminhtml\CaseInfo::getScoreClass
     * @magentoConfigFixture current_store fraud_protection/signifyd/active 1
     * @magentoDataFixture Magento/Signifyd/_files/case.php
     * @magentoAppArea adminhtml
     */
    public function testCaseEntityExists()
    {
        $this->order->loadByIncrementId('100000001');

        $block = $this->getBlock();
        self::assertNotEmpty($block->toHtml());
        self::assertStringContainsString((string) $block->getCaseGuaranteeDisposition(),$block->toHtml());
    }

    /**
     * Gets block.
     *
     * @return CaseInfo
     */
    private function getBlock()
    {
        $layout = $this->layoutFactory->create();

        $layout->addContainer('order_additional_info', 'Container');

        /** @var CaseInfo $block */
        $block = $layout->addBlock(CaseInfo::class, 'order_case_info', 'order_additional_info');
        $block->setAttribute('context', $this->getContext());
        $block->setTemplate('Magento_Signifyd::case_info.phtml');

        return $block;
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
