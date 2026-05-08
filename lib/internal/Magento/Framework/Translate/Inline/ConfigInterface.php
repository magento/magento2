<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Translate\Inline;

/**
 * Inline Translation config interface
 *
 * @api
 * @since 100.0.2
 */
interface ConfigInterface
{
    /**
     * Check whether inline translation is enabled
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $scope
     * @return bool
     */
    public function isActive($scope = null);

    /**
     * Check whether allowed client ip for inline translation
     *
     * @param null|string|bool|int|\Magento\Store\Model\Store $scope
     * @return bool
     */
    public function isDevAllowed($scope = null);
}
