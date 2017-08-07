<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Api;

use Magento\Theme\Api\Data\DesignConfigInterface;

/**
 * Design config CRUD interface.
 * @api
 * @since 2.1.0
 */
interface DesignConfigRepositoryInterface
{
    /**
     * Get design settings by scope
     *
     * @param string $scope
     * @param mixed $scopeId
     * @return DesignConfigInterface
     * @since 2.1.0
     */
    public function getByScope($scope, $scopeId);

    /**
     * Save design settings
     *
     * @param DesignConfigInterface $designConfig
     * @return DesignConfigInterface
     * @since 2.1.0
     */
    public function save(DesignConfigInterface $designConfig);

    /**
     * Delete design settings
     *
     * @param DesignConfigInterface $designConfig
     * @return DesignConfigInterface
     * @since 2.1.0
     */
    public function delete(DesignConfigInterface $designConfig);
}
