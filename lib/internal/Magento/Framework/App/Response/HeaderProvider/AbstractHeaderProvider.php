<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Response\HeaderProvider;

/**
 * Class to be used for setting headers with static values
 * @since 2.1.0
 */
abstract class AbstractHeaderProvider implements \Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface
{
    /**
     * @var string
     * @since 2.1.0
     */
    protected $headerName = '';

    /**
     * @var string
     * @since 2.1.0
     */
    protected $headerValue = '';

    /**
     * Whether the header should be attached to the response
     *
     * @return bool
     * @since 2.1.0
     */
    public function canApply()
    {
        return true;
    }

    /**
     * Get header name
     *
     * @return string
     * @since 2.1.0
     */
    public function getName()
    {
        return $this->headerName;
    }

    /**
     * Get header value
     *
     * @return string
     * @since 2.1.0
     */
    public function getValue()
    {
        return $this->headerValue;
    }
}
