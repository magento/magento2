<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\Controller\HttpHeaderProcessor;

use Magento\Framework\Exception\LocalizedException;
use Magento\GraphQl\Controller\HttpHeaderProcessorInterface;

/**
 * Processes the "Content-Type" header entry
 */
class ContentTypeProcessor implements HttpHeaderProcessorInterface
{
    /**
     * Handle the mandatory application/json header
     *
     * {@inheritDoc}
     * @throws LocalizedException
     */
    public function processHeaderValue(string $headerValue) : void
    {
        if (!$headerValue || strpos($headerValue, 'application/json') === false) {
            throw new LocalizedException(
                new \Magento\Framework\Phrase('Request content type must be application/json')
            );
        }
    }
}
