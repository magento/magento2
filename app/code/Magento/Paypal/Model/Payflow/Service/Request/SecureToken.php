<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Paypal\Model\Payflow\Service\Request;

use Magento\Framework\Math\Random;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Paypal\Model\Payflowpro;
use Magento\Quote\Model\Quote;

/**
 * Class for requesting a secure Payflow Pro token from Paypal
 */
class SecureToken
{
    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var Transparent
     */
    private $transparent;

    /**
     * @param UrlInterface $url
     * @param Random $mathRandom
     * @param Transparent $transparent
     */
    public function __construct(
        UrlInterface $url,
        Random $mathRandom,
        Transparent $transparent
    ) {
        $this->url = $url;
        $this->mathRandom = $mathRandom;
        $this->transparent = $transparent;
    }

    /**
     * Get the Secure Token from Paypal for TR
     *
     * @param Quote $quote
     * @param string[] $urls
     *
     * @return DataObject
     * @throws \Exception
     */
    public function requestToken(Quote $quote, array $urls = [])
    {
        $this->transparent->setStore($quote->getStoreId());
        $request = $this->transparent->buildBasicRequest();

        $request->setTrxtype(Payflowpro::TRXTYPE_AUTH_ONLY);
        $request->setVerbosity('HIGH');
        $request->setAmt(0);
        $request->setCurrency($quote->getBaseCurrencyCode());
        $request->setCreatesecuretoken('Y');
        $request->setSecuretokenid($this->mathRandom->getUniqueHash());
        $request->setReturnurl($urls['return_url'] ?? $this->url->getUrl('paypal/transparent/redirect'));
        $request->setErrorurl($urls['error_url'] ?? $this->url->getUrl('paypal/transparent/redirect'));
        $request->setCancelurl($urls['cancel_url'] ?? $this->url->getUrl('paypal/transparent/redirect'));
        $request->setDisablereceipt('TRUE');
        $request->setSilenttran('TRUE');

        $this->transparent->fillCustomerContacts($quote, $request);

        $result = $this->transparent->postRequest($request, $this->transparent->getConfig());

        return $result;
    }
}
