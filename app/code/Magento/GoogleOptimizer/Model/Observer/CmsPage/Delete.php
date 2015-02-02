<?php
/**
 * Google Experiment Cms Page Delete observer
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Model\Observer\CmsPage;

/**
 * Class Delete
 */
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
    public function deleteCmsGoogleExperimentScript($observer)
    {
        /** @var $cmsPage \Magento\Cms\Model\Page */
        $cmsPage = $observer->getEvent()->getObject();
        $this->_modelCode->loadByEntityIdAndType(
            $cmsPage->getId(),
            \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PAGE
        );
        if ($this->_modelCode->getId()) {
            $this->_modelCode->delete();
        }
        return $this;
    }
}
