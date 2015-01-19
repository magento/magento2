<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

/**
 * Interface DataProviderInterface
 * @package Magento\Framework\View\Element\UiComponent
 */
interface DataProviderInterface
{
    /**
     * Get meta data
     *
     * @return array
     */
    public function getMeta();

    /**
     * Get data
     *
     * @return array
     */
    public function getData();
}
