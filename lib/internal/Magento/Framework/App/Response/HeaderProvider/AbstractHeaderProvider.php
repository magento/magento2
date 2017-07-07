<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Response\HeaderProvider;

/**
 * Class to be used for setting headers with static values
 */
abstract class AbstractHeaderProvider implements \Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface
{
    /** @var string */
    protected $headerName = '';

    /** @var string */
    protected $headerValue = '';

    /**
     * Whether the header should be attached to the response
     *
     * @return bool
     */
    public function canApply()
    {
        return true;
    }

    /**
     * Get header name
     *
     * @return string
     */
    public function getName()
    {
        return $this->headerName;
    }

    /**
     * Get header value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->headerValue;
    }
}
