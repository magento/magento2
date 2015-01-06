<?php
/**
 * Product attribute edit form observer
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\LayeredNavigation\Block\Adminhtml\Product\Attribute\Grid;

use Magento\Framework\Module\Manager;

class Observer
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
     * @param \Magento\Framework\Event $event
     * @return void
     */
    public function observe($event)
    {
        if (!$this->moduleManager->isOutputEnabled('Magento_LayeredNavigation')) {
            return;
        }

        /** @var \Magento\Catalog\Block\Adminhtml\Product\Attribute\Grid $grid */
        $grid = $event->getGrid();

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
