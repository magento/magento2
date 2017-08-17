<?php
/**
 * Product attribute edit form observer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LayeredNavigation\Observer\Grid;

use Magento\Framework\Module\Manager;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class \Magento\LayeredNavigation\Observer\Grid\ProductAttributeGridBuildObserver
 *
 */
class ProductAttributeGridBuildObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param Manager $moduleManager
     */
    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->moduleManager->isOutputEnabled('Magento_LayeredNavigation')) {
            return;
        }

        /** @var \Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid $grid */
        $grid = $observer->getGrid();

        $grid->addColumnAfter(
            'is_filterable',
            [
                    'header' => __('Use in Layered Navigation'),
                    'sortable' => true,
                    'index' => 'is_filterable',
                    'type' => 'options',
                    'options' => [
                        '1' => __('Filterable (with results)'),
                        '2' => __('Filterable (no results)'),
                        '0' => __('No'),
                    ],
                    'align' => 'center',
            ],
            'is_searchable'
        );
    }
}
