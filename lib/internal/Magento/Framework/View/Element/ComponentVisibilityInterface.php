<?php
/**
 * Created by PhpStorm.
 * User: poluyano
 * Date: 12/4/2018
 * Time: 5:45 PM
 */

namespace Magento\Framework\View\Element;

/**
 * Interface which allows modify visibility behavior of UI components
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
