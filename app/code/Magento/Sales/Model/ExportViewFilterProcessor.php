<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

use Exception;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Component\Listing\Columns;

/**
 * Processor for checking custom view columns
 */
class ExportViewFilterProcessor
{
    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * @var array
     */
    private $currentConfig;

    /**
     * @var array
     */
    private $columnHeaders;

    /**
     * @var string
     */
    private $currentGridView;

    /**
     * @param BookmarkManagementInterface $bookmarkManagement
     */
    public function __construct(
        BookmarkManagementInterface $bookmarkManagement
    ) {
        $this->bookmarkManagement = $bookmarkManagement;
    }

    /**
     * Process activeColumns for current view for sales order reporting purpose
     *
     * @param UiComponentInterface $component
     * @param string $namespace
     * @param bool $isForHeading
     * @return array $activeColumnHeaders
     * @throws Exception
     */
    public function execute(UiComponentInterface $component, string $namespace, bool $isForHeading = false): array
    {
        return $this->getActiveColumnsForGrid($component, $namespace, $isForHeading);
    }

    /**
     * Returns column headers list
     *
     * @param UiComponentInterface $component
     *
     * @return array $columnHeaders[]
     * @throws Exception
     */
    private function getColumnsHeader(UiComponentInterface $component): array
    {
        $columnHeaders = [];
        $columns = $this->getColumnsComponent($component);
        foreach ($columns->getChildComponents() as $column) {
            if ($column->getData('config/label') && $column->getData('config/dataType') !== 'actions') {
                $columnHeaders[$column->getName()] = $column->getData('config/label');
            }
        }
        return $columnHeaders;
    }

    /**
     * Returns Columns component
     *
     * @param UiComponentInterface $component
     *
     * @return UiComponentInterface
     * @throws Exception
     */
    private function getColumnsComponent(UiComponentInterface $component): UiComponentInterface
    {
        foreach ($component->getChildComponents() as $childComponent) {
            if ($childComponent instanceof Columns) {
                return $childComponent;
            }
        }
        throw new Exception('No columns found'); // @codingStandardsIgnoreLine
    }

    /**
     * Get the current config details from the bookmark management
     *
     * @param string $namespace
     * @return array
     */
    private function getCurrentConfig(string $namespace): array
    {
        $currentConfig = [];
        $bookmarks = $this->bookmarkManagement->loadByNamespace($namespace);
        /** @var BookmarkInterface $bookmark */
        foreach ($bookmarks->getItems() as $bookmark) {
            if ($bookmark->isCurrent()) {
                $this->currentGridView = $bookmark->getIdentifier();
            }
            $currentConfig = array_merge_recursive($currentConfig, $bookmark->getConfig());
        }
        return $currentConfig;
    }

    /**
     * Retrieve activeColumns list for the grid
     *
     * @param UiComponentInterface $component
     * @param string $namespace
     * @param bool $isForHeading
     * @return array
     * @throws Exception
     */
    private function getActiveColumnsForGrid(
        UiComponentInterface $component,
        string $namespace,
        bool $isForHeading = false
    ): array {
        $activeColumns = [];
        $this->columnHeaders = $this->columnHeaders ?? $this->getColumnsHeader($component);
        $this->currentConfig = $this->currentConfig ?? $this->getCurrentConfig($namespace);
        if ($this->currentConfig && $this->currentGridView !== 'default'
            && $this->currentConfig['current']['columns']) {
            array_walk(
                $this->currentConfig['current']['columns'],
                function ($column, $key) use (&$activeColumns, &$isForHeading) {
                    if ($column['visible'] && array_key_exists($key, $this->columnHeaders)) {
                        $activeColumns[$this->currentConfig['current']['positions'][$key]] = $isForHeading
                            ? $this->columnHeaders[$key] : $key;
                    }
                }
            );
            if ($activeColumns) {
                ksort($activeColumns);
            }
        }
        return $activeColumns;
    }
}
