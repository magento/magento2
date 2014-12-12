<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Object;

/**
 * Interface IdentityInterface
 */
interface IdentityInterface
{
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities();
}
