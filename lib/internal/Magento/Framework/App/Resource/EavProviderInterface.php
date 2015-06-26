<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Resource;

use Magento\Framework\Exception\LocalizedException;

interface EavProviderInterface extends SourceProviderInterface
{
    /**
     * Add attribute to entities in collection
     *
     * If $attribute == '*' select all attributes
     *
     * @param array|string|integer|\Magento\Framework\App\Config\Element $attribute
     * @param bool|string $joinType flag for joining attribute
     * @return $this
     * @throws LocalizedException
     */
    public function addAttributeToSelect($attribute, $joinType = false);
}
