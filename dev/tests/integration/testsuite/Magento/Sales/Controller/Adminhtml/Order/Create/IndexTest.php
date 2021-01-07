<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order\Create;

use Magento\Backend\Model\Session\Quote;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Class checks create order index controller.
 *
 * @see \Magento\Sales\Controller\Adminhtml\Order\Create\Index
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class IndexTest extends AbstractBackendController
{
    /** @var Registry */
    private $registry;

    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var Quote */
    private $quoteSession;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = $this->_objectManager->get(Registry::class);
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
        $this->quoteSession = $this->_objectManager->get(Quote::class);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testExecute(): void
    {
        $customerId = 1;
        $editingOrderId = 10;

        $this->getRequest()->setMethod(Http::METHOD_GET);
        $this->getRequest()->setParam('customer_id', $customerId);
        $this->quoteSession->setOrderId($editingOrderId);
        $this->assertEquals($editingOrderId, $this->quoteSession->getOrderId());
        $this->dispatch('backend/sales/order_create/index');

        // Check that existing order in session was cleared
        $this->assertEquals(null, $this->quoteSession->getOrderId());

        $store = $this->storeManager->getStore();
        $this->assertEquals($customerId, $this->quoteSession->getCustomerId());
        $ruleData = $this->registry->registry('rule_data');
        $this->assertNotNull($ruleData);
        $this->assertEquals(
            ['store_id' => $store->getId(), 'website_id' => $store->getWebsiteId(), 'customer_group_id' => 1],
            $ruleData->getData()
        );
    }
}
