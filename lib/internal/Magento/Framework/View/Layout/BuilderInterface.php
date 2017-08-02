<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\View\LayoutInterface;

/**
 * Interface BuilderInterface
 *
 * @api
 * @since 2.0.0
 */
interface BuilderInterface
{
    /**
     * Build structure
     *
     * @return LayoutInterface
     * @since 2.0.0
     */
    public function build();
}
