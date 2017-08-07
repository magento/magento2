<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction;

use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * @api
 * @since 2.2.0
 */
interface VisibilityCheckerInterface extends ArgumentInterface
{
    /**
     * Check that action can be displayed on massaction list
     *
     * @return bool
     * @since 2.2.0
     */
    public function isVisible();
}
