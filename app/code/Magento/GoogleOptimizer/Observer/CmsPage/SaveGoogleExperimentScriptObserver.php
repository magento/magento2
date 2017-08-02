<?php
/**
 * Google Experiment Cms Page Save observer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleOptimizer\Observer\CmsPage;

use Magento\Framework\Event\Observer;

/**
 * Class \Magento\GoogleOptimizer\Observer\CmsPage\SaveGoogleExperimentScriptObserver
 *
 * @since 2.0.0
 */
class SaveGoogleExperimentScriptObserver extends \Magento\GoogleOptimizer\Observer\AbstractSave
{
    /**
     * @var \Magento\Cms\Model\Page
     * @since 2.0.0
     */
    protected $_page;

    /**
     * Init entity
     *
     * @param Observer $observer
     * @return void
     * @since 2.0.0
     */
    protected function _initEntity($observer)
    {
        $this->_page = $observer->getEvent()->getObject();
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
            'entity_type' => \Magento\GoogleOptimizer\Model\Code::ENTITY_TYPE_PAGE,
            'entity_id' => $this->_page->getId(),
            'store_id' => 0,
            'experiment_script' => $this->_params['experiment_script']
        ];
    }
}
