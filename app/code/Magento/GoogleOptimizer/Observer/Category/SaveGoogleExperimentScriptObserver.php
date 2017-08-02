<?php
/**
 * Google Experiment Category Save observer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Observer\Category;

use Magento\Framework\Event\Observer;

/**
 * Class \Magento\GoogleOptimizer\Observer\Category\SaveGoogleExperimentScriptObserver
 *
 * @since 2.0.0
 */
class SaveGoogleExperimentScriptObserver extends \Magento\GoogleOptimizer\Observer\AbstractSave
{
    /**
     * @var \Magento\Catalog\Model\Category
     * @since 2.0.0
     */
    protected $_category;

    /**
     * Init entity
     *
     * @param Observer $observer
     * @return void
     * @since 2.0.0
     */
    protected function _initEntity($observer)
    {
        $this->_category = $observer->getEvent()->getCategory();
    }

    /**
     * Check is Google Experiment enabled
     *
     * @return bool
     * @since 2.0.0
     */
    protected function _isGoogleExperimentActive()
    {
        return $this->_helper->isGoogleExperimentActive($this->_category->getStoreId());
    }

    /**
     * Get data for saving code model
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getCodeData()
    {
        return [
            'entity_type' => \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_CATEGORY,
            'entity_id' => $this->_category->getId(),
            'store_id' => $this->_category->getStoreId(),
            'experiment_script' => $this->_params['experiment_script']
        ];
    }
}
