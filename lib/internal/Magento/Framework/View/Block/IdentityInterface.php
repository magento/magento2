<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Block;

/**
 * Interface IdentityInterface
 */
interface IdentityInterface
{
    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities();
}
