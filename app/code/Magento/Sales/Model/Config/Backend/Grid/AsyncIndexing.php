<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Config\Backend\Grid;

/**
 * Backend model for global configuration value
 * 'dev/grid/async_indexing'.
 * @since 2.0.0
 */
class AsyncIndexing extends \Magento\Framework\App\Config\Value
{
    /**
     * Dispatches corresponding event after saving of configuration
     * value if it was changed.
     *
     * Dispatches next events:
     *
     * - config_data_dev_grid_async_indexing_enabled
     * - config_data_dev_grid_async_indexing_disabled
     *
     * @return $this
     * @since 2.0.0
     */
    public function afterSave()
    {
        if ($this->isValueChanged()) {
            $state = $this->getValue() ? 'enabled' : 'disabled';

            $this->_eventManager->dispatch(
                $this->_eventPrefix . '_dev_grid_async_indexing_' . $state,
                $this->_getEventData()
            );
        }

        return $this;
    }
}
