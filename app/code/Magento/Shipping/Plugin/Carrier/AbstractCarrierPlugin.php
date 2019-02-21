<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Shipping\Plugin\Carrier;

use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Framework\App\RequestInterface;

/**
 * Class AbstractCarrierPlugin
 * @package Magento\Shipping\Plugin\Carrier
 */
class AbstractCarrierPlugin
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
     * @param AbstractCarrier $carrier
     * @param bool $result
     * @return bool
     */
    public function afterIsActive(
        AbstractCarrier $carrier,
        $result
    ) {
        $website = $this->getRequest()->getParam('website');
        $store = $this->getRequest()->getParam('store');

        if ($store) {
            $carrier->setStore($store);
        } elseif ($website) {
            $carrier->setStore($website);
        } else {
            $carrier->setStore(0);
        }
        $activeField = $carrier->getConfigData('active');

        if ($activeField !== $result) {
            return $activeField == 1 || $activeField == 'true';
        }

        return $result;
    }

    /**
     * @return RequestInterface
     */
    private function getRequest()
    {
        return $this->request;
    }
}
