<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Class check address edit action
 *
 * @see \Magento\Sales\Controller\Adminhtml\Order\Address
 *
 * @magentoDbIsolation disabled
 * @magentoAppArea adminhtml
 */
class AddressTest extends AbstractBackendController
{
    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var Registry */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->orderFactory = $this->_objectManager->get(OrderInterfaceFactory::class);
        $this->registry = $this->_objectManager->get(Registry::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testSuccessfulEdit(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId(100000001);
        $this->dispatchWithAddressId((int)$order->getBillingAddressId());
        $this->assertInstanceOf(OrderAddressInterface::class, $this->registry->registry('order_address'));
    }

    /**
     * @return void
     */
    public function testWithNotExistingAddressId(): void
    {
        $this->dispatchWithAddressId(51728);
        $this->assertRedirect($this->stringContains('backend/sales/order/index/'));
    }

    /**
     * Dispatch request with address_id param
     *
     * @param int $addressId
     * @return void
     */
    private function dispatchWithAddressId(int $addressId): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setParam('address_id', $addressId);
        $this->dispatch('backend/sales/order/address');
    }
}
