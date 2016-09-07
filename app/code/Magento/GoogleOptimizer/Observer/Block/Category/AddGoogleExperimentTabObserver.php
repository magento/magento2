<?php
/**
 * Google Optimizer Observer Category Tab
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleOptimizer\Observer\Block\Category;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\GoogleOptimizer\Helper\Data;

class AddGoogleExperimentTabObserver implements ObserverInterface
{
    /**
     * @var string
     */
    public static $googleOptimizerBlockId ='google-experiment-form';

    /**
     * @var string
     */
    public static $googleOptimizerTabId ='google-experiment-tab';

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
                \Magento\GoogleOptimizer\Block\Adminhtml\Catalog\Category\Edit\Tab\Googleoptimizer::class,
                self::$googleOptimizerBlockId
            );

            /** @var $tabs \Magento\Catalog\Block\Adminhtml\Category\Tabs */
            $tabs = $observer->getEvent()->getTabs();
            $tabs->addTab(
                self::$googleOptimizerTabId,
                ['label' => __('Category View Optimization'), 'content' => $block->toHtml()]
            );
        }
    }
}
