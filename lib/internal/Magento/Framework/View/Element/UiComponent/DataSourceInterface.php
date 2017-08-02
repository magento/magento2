<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface DataSourceInterface
 * @since 2.0.0
 */
interface DataSourceInterface extends UiComponentInterface
{
    /**
     * @return DataProviderInterface
     * @since 2.0.0
     */
    public function getDataProvider();
}
