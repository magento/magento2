<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
