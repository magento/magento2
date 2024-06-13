<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Layer;

/**
 * @api
 * @since 100.0.2
 */
interface AvailabilityFlagInterface
{
    /**
     * Is filter enabled
     *
     * @param \Magento\Catalog\Model\Layer $layer
     * @param array $filters
     * @return bool
     */
    public function isEnabled($layer, array $filters = []);
}
