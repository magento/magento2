<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Controller\Adminhtml\Order;

use Magento\Framework\App\RequestInterface;

class ShipmentProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * ShipmentProvider constructor.
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Retrieve shipment
     *
     * @return array
     */
    public function getShipment()
    {
        return $this->request->getParam('shipment', []);
    }
}
