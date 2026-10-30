<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Config\Model\Config\Structure\Element;

/**
 * @api
 * @since 100.0.2
 */
class Tab extends AbstractComposite
{
    /**
     * Check whether tab is visible
     *
     * @return bool
     */
    public function isVisible()
    {
        return $this->hasChildren();
    }
}
