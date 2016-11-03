<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * Class ChangeOrderStatusToPaymentReviewStep
 *
 * This step changes order status via WEB API to "Payment Review", because Kount service always
 * return "Review" status for sandbox transactions.
 */
class ChangeOrderStatusToPaymentReviewStep implements TestStepInterface
{
    /**
     * @var string
     */
    private $orderId;

    /**
     * @var WebapiDecorator
     */
    private $webApi;

    /**
     * ChangeOrderStatusToPaymentReviewStep constructor.
     * @param $orderId
     * @param WebapiDecorator $webApi
     */
    public function __construct($orderId, WebapiDecorator $webApi)
    {
        $this->orderId = $orderId;
        $this->webApi = $webApi;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $order = $this->getOrder($this->orderId);
        $order['state'] = 'payment_review';
        $order['status'] = 'fraud';
        $this->saveOrder($order);
    }

    /**
     * Get order by increment id
     * @param string $incrementId
     * @return array
     */
    private function getOrder($incrementId)
    {
        $url = $_ENV['app_frontend_url'] . 'rest/V1/orders/';
        $url .= '?searchCriteria[filterGroups][0][filters][0][field]=increment_id';
        $url .= '&searchCriteria[filterGroups][0][filters][0][value]=' . $incrementId;
        $this->webApi->write($url, [], WebapiDecorator::GET);
        $response = json_decode($this->webApi->read(), true);
        $this->webApi->close();
        return $response['items'][0];
    }

    /**
     * Update order entity
     * @param array $order
     * @throws \Exception
     */
    private function saveOrder(array $order)
    {
        $url = $_ENV['app_frontend_url'] . 'rest/V1/orders';
        // web api doesn't allow to save payment additional information
        unset($order['payment']['additional_information']);
        $this->webApi->write($url, ['entity' => $order], WebapiDecorator::POST);
        $response = json_decode($this->webApi->read(), true);
        $this->webApi->close();
        if (empty($response['entity_id'])) {
            throw new \Exception('Couldn\'t update order details');
        }
    }
}
