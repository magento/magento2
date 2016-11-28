<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * Get successfully placed order id.
 */
class GetPlacedOrderIdStep implements TestStepInterface
{
    /**
     * Order success page.
     *
     * @var CheckoutOnepageSuccess
     */
    private $checkoutOnepageSuccess;

    /**
     * Curl transport on webapi.
     *
     * @var WebapiDecorator
     */
    private $decorator;

    /**
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param WebapiDecorator $decorator
     */
    public function __construct(
        CheckoutOnepageSuccess $checkoutOnepageSuccess,
        WebapiDecorator $decorator
    ) {
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
        $this->decorator = $decorator;
    }

    /**
     * Get success placed order id.
     *
     * @return array
     */
    public function run()
    {
        return [
            'orderId' => $this->getOrderId($this->checkoutOnepageSuccess->getSuccessBlock()->getGuestOrderId())
        ];
    }

    /**
     * Get order id by increment id.
     *
     * @param string $incrementId
     * @return string
     */
    private function getOrderId($incrementId)
    {
        $url = $_ENV['app_frontend_url'] . 'rest/V1/orders/';
        $url .= '?searchCriteria[filterGroups][0][filters][0][field]=increment_id';
        $url .= '&searchCriteria[filterGroups][0][filters][0][value]=' . $incrementId;
        $this->decorator->write($url, [], WebapiDecorator::GET);
        $response = json_decode($this->decorator->read(), true);
        $this->decorator->close();
        return $response['items'][0]['entity_id'];
    }
}
