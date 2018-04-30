<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Shipping\Model;

use Magento\Framework\App\RequestInterface;

/**
 * @inheritdoc
 */
class ShipmentProvider implements ShipmentProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function getShipmentData(): array
    {
        return $this->request->getParam('shipment', []);
    }
}
