<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PaypalGraphQl\Model\Plugin;

use Magento\Framework\DataObject;
use Magento\Store\Model\StoreRepository;
use Magento\PaypalGraphQl\Model\Resolver\Store\Url;

/**
 * Plugin for PayflowLink payment model class
 */
class Payflowlink
{
    /**
     * @var Url
     */
    private $url;

    /**
     * @var StoreRepository
     */
    private $storeRepository;

    /**
     * @param Url $url
     * @param StoreRepository $storeRepository
     */
    public function __construct(Url $url, StoreRepository $storeRepository)
    {
        $this->url = $url;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Update redirect URLs in request with values stored in payment additionalInformation
     *
     * Relative URL paths are converted to absolute URLs
     *
     * @param \Magento\Paypal\Model\Payflowlink $subject
     * @param DataObject $request
     * @return mixed
     */
    public function afterBuildBasicRequest(
        \Magento\Paypal\Model\Payflowlink $subject,
        DataObject $request
    ): DataObject {
        $payment = $subject->getInfoInstance();
        $storeId = $subject->getData('store');
        $store = $this->storeRepository->getById($storeId);

        $cancelUrl = $payment->getAdditionalInformation('cancel_url');
        if ($cancelUrl) {
            $request->setCancelurl($this->url->getUrlFromPath($cancelUrl, $store));
        }

        $returnUrl = $payment->getAdditionalInformation('return_url');
        if ($returnUrl) {
            $request->setReturnurl($this->url->getUrlFromPath($returnUrl, $store));
        }

        $errorUrl = $payment->getAdditionalInformation('error_url');
        if ($errorUrl) {
            $request->setErrorurl($this->url->getUrlFromPath($errorUrl, $store));
        }

        return $request;
    }
}
