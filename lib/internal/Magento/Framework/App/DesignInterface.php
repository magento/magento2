<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Design Interface
 */
interface DesignInterface
{
    /**
     * Load custom design settings for specified store and date
     *
     * @param string $storeId
     * @param string|null $date
     * @return $this
     */
    public function loadChange($storeId, $date = null);

    /**
     * Apply design change from self data into specified design package instance
     *
     * @param \Magento\Framework\View\DesignInterface $packageInto
     * @return $this
     */
    public function changeDesign(\Magento\Framework\View\DesignInterface $packageInto);
}
