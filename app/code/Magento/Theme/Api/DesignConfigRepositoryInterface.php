<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Api;

use Magento\Theme\Api\Data\DesignConfigInterface;

/**
 * Design config CRUD interface.
 */
interface DesignConfigRepositoryInterface
{
    /**
     * Save design settings
     *
     * @param DesignConfigInterface $designConfig
     * @return DesignConfigInterface
     */
    public function save(DesignConfigInterface $designConfig);

}
