<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Api;

use Magento\Theme\Api\Data\DesignConfigInterface;

/**
 * Design config CRUD interface.
 * @api
 */
interface DesignConfigRepositoryInterface
{
    /**
     * Get design settings by scope
     *
     * @param string $scope
     * @param mixed $scopeId
     * @return DesignConfigInterface
     */
    public function getByScope($scope, $scopeId);

    /**
     * Save design settings
     *
     * @param DesignConfigInterface $designConfig
     * @return DesignConfigInterface
     */
    public function save(DesignConfigInterface $designConfig);

    /**
     * Delete design settings
     *
     * @param DesignConfigInterface $designConfig
     * @return DesignConfigInterface
     */
    public function delete(DesignConfigInterface $designConfig);
}
