<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Structure\Element;

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
