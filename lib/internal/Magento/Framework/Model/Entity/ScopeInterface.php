<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

/**
 * Interface ScopeInterface
 */
interface ScopeInterface
{
    /**
     * @return string
     */
    public function getValue();

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return ScopeInterface|null
     */
    public function getFallback();
}
