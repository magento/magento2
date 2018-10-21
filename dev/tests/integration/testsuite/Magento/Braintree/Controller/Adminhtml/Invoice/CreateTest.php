<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Braintree\Controller\Adminhtml\Invoice;

use Braintree\Result\Successful;
use Braintree\Transaction;
use Magento\Braintree\Model\Adapter\BraintreeAdapter;
use Magento\Braintree\Model\Adapter\BraintreeAdapterFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Message\MessageInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * @magentoAppArea adminhtml
 */
class CreateTest extends AbstractBackendController
{
    /**
     * @var BraintreeAdapter|MockObject
     */
    private $adapter;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $adapterFactory = $this->getMockBuilder(BraintreeAdapterFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->adapter = $this->getMockBuilder(BraintreeAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $adapterFactory->method('create')
            ->willReturn($this->adapter);

        $this->_objectManager->addSharedInstance($adapterFactory, BraintreeAdapterFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->_objectManager->removeSharedInstance(BraintreeAdapterFactory::class);
        parent::tearDown();
    }

    /**
     * Checks a case when non default Merchant Account ID should be send to Braintree
     * during creation second partial invoice.
     *
     * @magentoConfigFixture default_store payment/braintree/merchant_account_id Magneto
     * @magentoConfigFixture current_store payment/braintree/merchant_account_id USA_Merchant
     * @magentoDataFixture Magento/Braintree/Fixtures/partial_invoice.php
     */
    public function testCreatePartialInvoiceWithNonDefaultMerchantAccount()
    {
        $order = $this->getOrder('100000002');

        $this->adapter->method('sale')
            ->with(self::callback(function ($request) {
                self::assertEquals('USA_Merchant', $request['merchantAccountId']);
                return true;
            }))
            ->willReturn($this->getTransactionStub());

        $uri = 'backend/sales/order_invoice/save/order_id/' . $order->getEntityId();
        $this->prepareRequest($uri);
        $this->dispatch($uri);

        self::assertSessionMessages(
            self::equalTo(['The invoice has been created.']),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Creates stub for Braintree capture Transaction.
     *
     * @return Successful
     */
    private function getTransactionStub(): Successful
    {
        $transaction = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $transaction->status = 'submitted_for_settlement';
        $response = new Successful();
        $response->success = true;
        $response->transaction = $transaction;

        return $response;
    }

    /**
     * Gets order by increment ID.
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    private function getOrder(string $incrementId): OrderInterface
    {
        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $this->_objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter('increment_id', $incrementId)
            ->create();

        /** @var OrderRepositoryInterface $repository */
        $repository = $this->_objectManager->get(OrderRepositoryInterface::class);
        $items = $repository->getList($searchCriteria)
            ->getItems();

        return array_pop($items);
    }

    /**
     * Prepares POST request for invoice creation.
     *
     * @param string $uri
     */
    private function prepareRequest(string $uri)
    {
        /** @var FormKey $formKey */
        $formKey = $this->_objectManager->get(FormKey::class);
        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setParam('form_key', $formKey->getFormKey());
        $request->setRequestUri($uri);
        $request->setPostValue(
            [
                'invoice' => [
                    'capture_case' => 'online'
                ]
            ]
        );
    }
}
