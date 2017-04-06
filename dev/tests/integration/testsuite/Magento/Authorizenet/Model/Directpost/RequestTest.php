<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Authorizenet\Model\Directpost;

use Magento\Authorizenet\Model\Directpost;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class contains tests for Authorize.net Direct Post request handler
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->order = $this->getOrder();
        $this->request = $this->objectManager->get(Request::class);
    }

    /**
     * @covers \Magento\Authorizenet\Model\Directpost\Request::setDataFromOrder
     * @magentoDataFixture Magento/Authorizenet/_files/order.php
     */
    public function testSetDataFromOrder()
    {
        $customerEmail = 'john.doe@example.com';
        $merchantEmail = 'merchant@example.com';

        /** @var Directpost|MockObject $payment */
        $payment = $this->getMockBuilder(Directpost::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfigData'])
            ->getMock();

        $payment->expects(static::exactly(2))
            ->method('getConfigData')
            ->willReturnMap([
                ['email_customer', null, $customerEmail],
                ['merchant_email', null, $merchantEmail]
            ]);

        $result = $this->request->setDataFromOrder($this->order, $payment);

        static::assertEquals('US', $result->getXCountry());
        static::assertEquals('UK', $result->getXShipToCountry());
        static::assertEquals($customerEmail, $result->getXEmailCustomer());
        static::assertEquals($merchantEmail, $result->getXMerchantEmail());
    }

    /**
     * Get stored order
     * @return Order
     */
    private function getOrder()
    {
        /** @var FilterBuilder $filterBuilder */
        $filterBuilder = $this->objectManager->get(FilterBuilder::class);
        $filters = [
            $filterBuilder->setField(OrderInterface::INCREMENT_ID)
                ->setValue('100000002')
                ->create()
        ];

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilters($filters)
            ->create();

        $orderRepository = $this->objectManager->get(OrderRepositoryInterface::class);
        $orders = $orderRepository->getList($searchCriteria)
            ->getItems();

        /** @var OrderInterface $order */
        return array_pop($orders);
    }
}
