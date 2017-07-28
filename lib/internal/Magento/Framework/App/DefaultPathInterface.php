<?php
/**
 * Abstract application router
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Interface \Magento\Framework\App\DefaultPathInterface
 *
 * @since 2.0.0
 */
interface DefaultPathInterface
{
    /**
     * @param string $code
     * @return string
     * @since 2.0.0
     */
    public function getPart($code);
}
