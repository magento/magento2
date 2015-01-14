<?php
/**
 * Google Experiment Category Delete observer
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Model\Observer\Category;

class Delete
{
    /**
     * @var \Magento\GoogleOptimizer\Model\Code
     */
    protected $_modelCode;

    /**
     * @param \Magento\GoogleOptimizer\Model\Code $modelCode
     */
    public function __construct(\Magento\GoogleOptimizer\Model\Code $modelCode)
    {
        $this->_modelCode = $modelCode;
    }

    /**
     * Delete Product scripts after deleting product
     *
     * @param \Magento\Framework\Object $observer
     * @return $this
     */
    public function deleteCategoryGoogleExperimentScript($observer)
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
