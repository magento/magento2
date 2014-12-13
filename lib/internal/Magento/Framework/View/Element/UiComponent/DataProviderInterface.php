<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
