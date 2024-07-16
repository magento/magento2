<?php
declare(strict_types=1);

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\HeaderProvider;

/**
 * Adds an Referrer-Policy header to HTTP responses to controls how much referrer information.
 */
class ReferrerPolicy extends \Magento\Framework\App\Response\HeaderProvider\AbstractHeaderProvider
{
    /**
     * @var string
     */
    protected $headerValue = 'strict-origin-when-cross-origin';

    /**
     * @var string
     */
    protected $headerName = 'Referrer-Policy';
}
