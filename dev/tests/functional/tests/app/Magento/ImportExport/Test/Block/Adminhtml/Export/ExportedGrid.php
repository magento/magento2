<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Block\Adminhtml\Export;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;

/**
 * List of exported files
 */
class ExportedGrid extends DataGrid
{
    /**
     * Locator value for "Download" link inside action column.
     *
     * @var string
     */
    protected $editLink = '//a[@class="action-menu-item"][text()="Download"]';

    /**
     * First row in the grid selector
     *
     * @var string
     */
    protected $firstRowSelector = '//tr[@data-repeat-index="0"]';

    /**
     * Select action toggle.
     *
     * @var string
     */
    private $selectAction = '.action-select';

    /**
     * Locator value for "Delete" link inside action column.
     *
     * @var string
     */
    private $deleteLink = '//a[@class="action-menu-item"][text()="Delete"]';

    /**
     * Exported grid locator
     *
     * @var string
     */
    private $exportGrid = '.data-grid';

    /**
     * Delete all files from exported grid
     */
    public function deleteAllExportedFiles()
    {
        $this->waifForGrid();
        $firstGridRow = $this->getFirstRow();
        while ($firstGridRow->isVisible()) {
            $this->deleteFile($firstGridRow);
        }
    }

    /**
     * Delete exported file from the grid
     *
     * @param SimpleElement $rowItem
     * @return void
     */
    private function deleteFile(SimpleElement $rowItem)
    {
        $rowItem->find($this->selectAction)->click();
        $rowItem->find($this->deleteLink, Locator::SELECTOR_XPATH)->click();
        $this->confirmDeleteModal();
        $this->waitLoader();
    }

    /**
     * Get first row from the grid
     *
     * @return SimpleElement
     */
    public function getFirstRow(): SimpleElement
    {
        return $this->_rootElement->find($this->firstRowSelector, \Magento\Mtf\Client\Locator::SELECTOR_XPATH);
    }

    /**
     * Download first exported file
     *
     * @throws \Exception
     */
    public function downloadFirstFile()
    {
        $this->waifForGrid();
        $firstRow = $this->getFirstRow();
        $i = 0;
        while (!$firstRow->isVisible()) {
            if ($i === 10) {
                throw new \Exception('There is no exported file in the grid');
            }
            $this->browser->refresh();
            $this->waifForGrid();
            ++$i;
        }
        $this->clickDownloadLink($firstRow);
    }

    /**
     * Wait for the grid
     *
     * @return void
     */
    public function waifForGrid()
    {
        $this->waitForElementVisible($this->exportGrid);
        $this->waitLoader();
    }

    /**
     * Click on "Download" link.
     *
     * @param SimpleElement $rowItem
     * @return void
     */
    private function clickDownloadLink(SimpleElement $rowItem)
    {
        $rowItem->find($this->selectAction)->click();
        $rowItem->find($this->editLink, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Confirm delete file modal
     *
     * @return void
     */
    private function confirmDeleteModal()
    {
        $modalElement = $this->browser->find($this->confirmModal);
        /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
        $modal = $this->blockFactory->create(
            \Magento\Ui\Test\Block\Adminhtml\Modal::class,
            ['element' => $modalElement]
        );
        $modal->acceptAlert();
    }
}
