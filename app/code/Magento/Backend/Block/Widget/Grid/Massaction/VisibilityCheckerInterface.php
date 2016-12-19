<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Widget\Grid\Massaction;

interface VisibilityCheckerInterface
{
    /**
     * Check that action can be displayed on massaction list
     *
     * @return bool
     */
    public function isVisible();
}
