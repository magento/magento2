<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

/**
 * Class ScopeProviderInterface
 */
interface ScopeProviderInterface
{
    /**
     * @return \Magento\Framework\Model\Entity\ScopeInterface[]
     */
    public function getContext();
}
