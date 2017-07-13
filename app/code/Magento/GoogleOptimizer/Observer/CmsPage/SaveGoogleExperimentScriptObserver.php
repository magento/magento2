<?php
/**
 * Google Experiment Cms Page Save observer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Observer\CmsPage;

use Magento\Framework\Event\Observer;

class SaveGoogleExperimentScriptObserver extends \Magento\GoogleOptimizer\Observer\AbstractSave
{
    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_page;

    /**
     * Init entity
     *
     * @param Observer $observer
     * @return void
     */
    protected function _initEntity($observer)
    {
        $this->_page = $observer->getEvent()->getObject();
    }

    /**
     * Get data for saving code model
     *
     * @return array
     */
    protected function _getCodeData()
    {
        return [
            'entity_type' => \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PAGE,
            'entity_id' => $this->_page->getId(),
            'store_id' => 0,
            'experiment_script' => $this->_params['experiment_script']
        ];
    }
}
