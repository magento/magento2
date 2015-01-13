<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Api\Data;

interface AttributeFrontendLabelInterface
{
    /**
     * Return store id
     *
     * @return int|null
     */
    public function getStoreId();

    /**
     * Return label
     *
     * @return string|null
     */
    public function getLabel();
}
