<?php
/**
 * Google Experiment Category Delete observer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Observer\Category;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\GoogleOptimizer\Observer\Category\DeleteCategoryGoogleExperimentScriptObserver
 *
 * @since 2.0.0
 */
class DeleteCategoryGoogleExperimentScriptObserver implements ObserverInterface
{
    /**
     * @var \Magento\GoogleOptimizer\Model\Code
     * @since 2.0.0
     */
    protected $_modelCode;

    /**
     * @param \Magento\GoogleOptimizer\Model\Code $modelCode
     * @since 2.0.0
     */
    public function __construct(\Magento\GoogleOptimizer\Model\Code $modelCode)
    {
        $this->_modelCode = $modelCode;
    }

    /**
     * Delete Product scripts after deleting product
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     * @since 2.0.0
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $category \Magento\Catalog\Model\Category */
        $category = $observer->getEvent()->getCategory();
        $this->_modelCode->loadByEntityIdAndType(
            $category->getId(),
            \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_CATEGORY,
            $category->getStoreId()
        );
        if ($this->_modelCode->getId()) {
            $this->_modelCode->delete();
        }
        return $this;
    }
}
