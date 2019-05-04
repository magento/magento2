<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Service\Request;

use Magento\Framework\Math\Random;
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
use Magento\Paypal\Model\Payflow\Transparent;
use Magento\Paypal\Model\Payflowpro;
use Magento\Quote\Model\Quote;

/**
 * Class SecureToken
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
     *
     * @return DataObject
     * @throws \Exception
     */
    public function requestToken(Quote $quote)
    {
        $this->transparent->setStore($quote->getStoreId());
        $request = $this->transparent->buildBasicRequest();

        $request->setTrxtype(Payflowpro::TRXTYPE_AUTH_ONLY);
        $request->setVerbosity('HIGH');
        $request->setAmt(0);
        $request->setCurrency($quote->getBaseCurrencyCode());
        $request->setCreatesecuretoken('Y');
        $request->setSecuretokenid($this->mathRandom->getUniqueHash());
        $request->setReturnurl($this->url->getUrl('paypal/transparent/response'));
        $request->setErrorurl($this->url->getUrl('paypal/transparent/response'));
        $request->setCancelurl($this->url->getUrl('paypal/transparent/cancel'));
        $request->setDisablereceipt('TRUE');
        $request->setSilenttran('TRUE');

        $this->transparent->fillCustomerContacts($quote, $request);

        $result = $this->transparent->postRequest($request, $this->transparent->getConfig());

        return $result;
    }
}
