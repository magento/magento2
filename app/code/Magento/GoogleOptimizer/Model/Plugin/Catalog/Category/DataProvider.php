<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleOptimizer\Model\Plugin\Catalog\Category;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class DataProvider
{
    /**
     * @var \Magento\GoogleOptimizer\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @param \Magento\GoogleOptimizer\Helper\Data $helper
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(\Magento\GoogleOptimizer\Helper\Data $helper, \Magento\Framework\View\LayoutInterface $layout)
    {
        $this->_helper = $helper;
        $this->_layout = $layout;
    }

    /**
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPrepareMeta(\Magento\Catalog\Model\Category\DataProvider $subject, $result)
    {
        $result['general']['fields']['google_experiment_container']['visible'] =
            $this->_helper->isGoogleExperimentActive();

        return $result;
    }
}
