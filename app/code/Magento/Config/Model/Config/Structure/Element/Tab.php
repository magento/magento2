<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Element;

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
