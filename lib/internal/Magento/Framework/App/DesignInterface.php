<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Design Interface
 * @since 2.0.0
 */
interface DesignInterface
{
    /**
     * Load custom design settings for specified store and date
     *
     * @param string $storeId
     * @param string|null $date
     * @return $this
     * @since 2.0.0
     */
    public function loadChange($storeId, $date = null);

    /**
     * Apply design change from self data into specified design package instance
     *
     * @param \Magento\Framework\View\DesignInterface $packageInto
     * @return $this
     * @since 2.0.0
     */
    public function changeDesign(\Magento\Framework\View\DesignInterface $packageInto);
}
