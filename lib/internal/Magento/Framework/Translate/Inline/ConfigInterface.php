<?php
/**
 * Inline Translation config interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Translate\Inline;

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
