<?php
/**
 * Google Experiment Product Save observer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Observer\Product;

use Magento\Framework\Event\Observer;

/**
 * Class \Magento\GoogleOptimizer\Observer\Product\SaveGoogleExperimentScriptObserver
 *
 * @since 2.0.0
 */
class SaveGoogleExperimentScriptObserver extends \Magento\GoogleOptimizer\Observer\AbstractSave
{
    /**
     * @var \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    protected $_product;

    /**
     * Init entity
     *
     * @param Observer $observer
     * @return void
     * @since 2.0.0
     */
    protected function _initEntity($observer)
    {
        $this->_product = $observer->getEvent()->getProduct();
    }

    /**
     * Check is Google Experiment enabled
     *
     * @return bool
     * @since 2.0.0
     */
    protected function _isGoogleExperimentActive()
    {
        return $this->_helper->isGoogleExperimentActive($this->_product->getStoreId());
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
            'entity_type' => \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PRODUCT,
            'entity_id' => $this->_product->getId(),
            'store_id' => $this->_product->getStoreId(),
            'experiment_script' => $this->_params['experiment_script']
        ];
    }
}
