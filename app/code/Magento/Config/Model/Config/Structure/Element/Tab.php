<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure\Element;

/**
 * @api
 * @since 2.0.0
 */
class Tab extends AbstractComposite
{
    /**
     * Check whether tab is visible
     *
     * @return bool
     * @since 2.0.0
     */
    public function isVisible()
    {
        return $this->hasChildren();
    }
}
