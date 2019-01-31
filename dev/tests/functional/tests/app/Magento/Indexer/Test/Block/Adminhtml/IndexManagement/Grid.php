<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Indexer\Test\Block\Adminhtml\IndexManagement;

use Magento\Mtf\Client\Locator;
use Magento\Backend\Test\Block\Widget\Grid as WidgetGrid;

/**
 * Grid in Index Management Page.
 */
class Grid extends WidgetGrid
{
    /**
     * Select action toggle.
     *
     * @var string
     */
    private $selectAction = './/*[@id=\'gridIndexer_massaction-select\']/option[.=\'Update by Schedule\']';

    /**
     * Select Submit Button.
     *
     * @var string
     */
    private $updateButton = './/button[@title=\'Submit\']';

    /**
     * Indexer Status locator.
     *
     * @var string
     */
    private $indxerStatus = './/*[@data-column=\'indexer_status\']/span/span';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'Indexer' => [
            'selector' => '[name="indexer_ids"]',
            'input' => 'checkbox',
            'value' => 'Yes',
        ],
    ];

    /**
     * Update indexers action in Index Management Page.
     *
     * @param array $indexers
     * @throws \Exception
     * @return void
     */
    public function updateBySchedule(array $indexers)
    {
        foreach ($indexers as $indexer) {
            $selectItem = $this->getRow(['Indexer' => trim($indexer)])->find($this->selectItem);
            if ($selectItem->isVisible()) {
                $selectItem->click();
            } else {
                throw new \Exception("Searched item was not found by filter\n" . print_r($indexer, true));
            }
        }
        $this->_rootElement->find($this->selectAction, Locator::SELECTOR_XPATH, 'select')->click();
        $this->_rootElement->find($this->updateButton, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Return indexers status in Index Management Page.
     *
     * @param string $indexer
     * @return string|array
     */
    public function getIndexerStatus($indexer)
    {
        return $this->getRow(['Indexer' => trim($indexer)])
            ->find($this->indxerStatus, Locator::SELECTOR_XPATH)->getText();
    }
}
