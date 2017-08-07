<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

/**
 * Interface ScopeInterface
 * @since 2.1.0
 */
interface ScopeInterface
{
    /**
     * @return string
     * @since 2.1.0
     */
    public function getValue();

    /**
     * @return string
     * @since 2.1.0
     */
    public function getIdentifier();

    /**
     * @return ScopeInterface|null
     * @since 2.1.0
     */
    public function getFallback();
}
