<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Guest;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Helper\Guest;
use Magento\TestFramework\Request;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test for orders and returns controller.
 *
 * @see \Magento\Sales\Controller\Guest\View
 */
class ViewTest extends AbstractController
{
    /** @var CookieManagerInterface */
    private $cookieManager;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->cookieManager = $this->_objectManager->get(CookieManagerInterface::class);
        $this->orderFactory = $this->_objectManager->get(OrderInterfaceFactory::class);
        $this->orderRepository = $this->_objectManager->get(OrderRepositoryInterface::class);
    }

    /**
     * Check that controller applied GET requests.
     *
     * @return void
     */
    public function testExecuteWithGetRequest(): void
    {
        $this->getRequest()->setMethod(Request::METHOD_GET);
        $this->dispatch('sales/guest/view/');

        $this->assertRedirect($this->stringContains('sales/guest/form'));
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testExecuteWithWrongCookie(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $order->setProtectCode('0e6640');
        $this->orderRepository->save($order);
        $cookieValue = base64_encode('0' . ':' . $order->getIncrementId());
        $this->cookieManager->setPublicCookie(Guest::COOKIE_NAME, $cookieValue);
        $this->getRequest()->setMethod(Request::METHOD_GET);
        $this->dispatch('sales/guest/view/');
        $this->assertRedirect($this->stringContains('sales/guest/form/'));
        $this->assertSessionMessages(
            $this->containsEqual((string)__('You entered incorrect data. Please try again.'))
        );
    }
}
