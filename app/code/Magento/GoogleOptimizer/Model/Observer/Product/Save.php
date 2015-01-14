<?php
/**
 * Google Experiment Product Save observer
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Model\Observer\Product;

use Magento\Framework\Event\Observer;

class Save extends \Magento\GoogleOptimizer\Model\Observer\AbstractSave
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * Init entity
     *
     * @param Observer $observer
     * @return void
     */
    protected function _initEntity($observer)
    {
        $this->_product = $observer->getEvent()->getProduct();
    }

    /**
     * Check is Google Experiment enabled
     *
     * @return bool
     */
    protected function _isGoogleExperimentActive()
    {
        return $this->_helper->isGoogleExperimentActive($this->_product->getStoreId());
    }

    /**
     * Get data for saving code model
     *
     * @return array
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
