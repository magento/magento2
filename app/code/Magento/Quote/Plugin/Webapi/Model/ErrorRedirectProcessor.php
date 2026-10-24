<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Plugin\Webapi\Model;

use Magento\Framework\Validator\Exception as ValidatorException;
use Magento\Framework\Webapi\Rest\Response as RestResponse;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteValidator;

class ErrorRedirectProcessor
{
    /**
     * @param RestResponse $restResponse
     */
    public function __construct(
        private readonly RestResponse $restResponse
    ) {
    }

    /**
     * Set errorRedirectAction in case of exception.
     *
     * @param QuoteValidator $subject
     * @param callable $proceed
     * @param Quote $quote
     */
    public function aroundValidateBeforeSubmit(QuoteValidator $subject, callable $proceed, Quote $quote)
    {
        try {
            $result = $proceed($quote);
        } catch (ValidatorException $e) {
            $this->restResponse->setHeader('errorRedirectAction', '#shipping');
            throw $e;
        }

        return $result;
    }
}
