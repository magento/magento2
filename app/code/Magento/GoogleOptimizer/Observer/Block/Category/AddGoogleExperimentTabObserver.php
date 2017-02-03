<?php
/**
 * Google Optimizer Observer Category Tab
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleOptimizer\Observer\Block\Category;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\GoogleOptimizer\Helper\Data;
use Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Category\Edit\Tab\Googleoptimizer;

class AddGoogleExperimentTabObserver implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var LayoutInterface
     */
    protected $_layout;

    /**
     * @param Data $helper
     * @param LayoutInterface $layout
     */
    public function __construct(Data $helper, LayoutInterface $layout)
    {
        $this->_helper = $helper;
        $this->_layout = $layout;
    }

    /**
     * Adds Google Experiment tab to the category edit page
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $storeId = $observer->getEvent()->getTabs()->getCategory()->getStoreId();
        if ($this->_helper->isGoogleExperimentActive($storeId)) {
            $block = $this->_layout->createBlock(
                Googleoptimizer::class,
                'google-experiment-form'
            );

            /** @var $tabs \Magento\Catalog\Block\Adminhtml\Category\Tabs */
            $tabs = $observer->getEvent()->getTabs();
            $tabs->addTab(
                'google-experiment-tab',
                ['label' => __('Category View Optimization'), 'content' => $block->toHtml()]
            );
        }
    }
}
