<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Shipping\Model;

use Magento\Framework\App\RequestInterface;

class ShipmentProvider implements ShipmentProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Retrieve shipment items from request
     *
     * @return array|null
     */
    public function getShipment()
    {
        return $this->request->getParam('shipment');
    }
}
