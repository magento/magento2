<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Response\HeaderProvider;

/**
 * Interface \Magento\Framework\App\Response\HeaderProvider\HeaderProviderInterface
 *
 * @since 2.1.0
 */
interface HeaderProviderInterface
{
    /**
     * Whether the header should be attached to the response
     *
     * @return bool
     * @since 2.1.0
     */
    public function canApply();

    /**
     * Header name
     *
     * @return string
     * @since 2.1.0
     */
    public function getName();

    /**
     * Header value
     *
     * @return string
     * @since 2.1.0
     */
    public function getValue();
}
