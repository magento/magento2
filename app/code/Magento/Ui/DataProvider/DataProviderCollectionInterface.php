<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProviderInterface;

/**
 * Interface DataProviderCollectionInterface
 */
interface DataProviderCollectionInterface extends DataProviderInterface
{
    /**
     * Add a filter to the data
     *
     * @param array $filter
     * @return void
     */
    public function addFilter(array $filter);

    /**
     * Get data
     *
     * @return \Magento\Framework\Object[]
     */
    public function getData();
}
