<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface DataSourceInterface
 *
 * @api
 */
interface DataSourceInterface extends UiComponentInterface
{
    /**
     * @return DataProviderInterface
     */
    public function getDataProvider();
}
