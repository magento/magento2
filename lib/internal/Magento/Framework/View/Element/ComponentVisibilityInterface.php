<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element;

/**
 * Interface which allows to modify visibility behavior of UI components
 *
 * @api
 */
interface ComponentVisibilityInterface
{
    /**
     * Defines if the component can be shown
     *
     * @return bool
     */
    public function isComponentVisible(): bool;
}
