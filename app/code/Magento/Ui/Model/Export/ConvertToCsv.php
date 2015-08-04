<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\Api\Search\DocumentInterface;

/**
 * Class ConvertToCsv
 */
class ConvertToCsv
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $directory;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param Filter $filter
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        Filter $filter
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
    }

    /**
     * Returns Columns component
     *
     * @param UiComponentInterface $component
     * @return UiComponentInterface
     * @throws \Exception
     */
    protected function getColumnsComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $childComponent) {
            if ($childComponent instanceof \Magento\Ui\Component\Listing\Columns) {
                return $childComponent;
            }
        }
        throw new \Exception('No columns found');
    }

    /**
     * Returns columns list
     *
     * @param UiComponentInterface $component
     * @return UiComponentInterface[]
     */
    protected function getColumns(UiComponentInterface $component)
    {
        if (!isset($this->columns[$component->getName()])) {
            $columns = $this->getColumnsComponent($component);
            foreach ($columns->getChildComponents() as $column) {
                $this->columns[$component->getName()][$column->getName()] = $column;
            }
        }
        return $this->columns[$component->getName()];
    }

    /**
     * Retrieve Headers row array for Export
     *
     * @param UiComponentInterface $component
     * @return string[]
     */
    protected function getHeaders(UiComponentInterface $component)
    {
        $row = [];
        foreach ($this->getColumns($component) as $column) {
            if ($column->getData('config/label')) {
                $row[] = $column->getData('config/label');
            }
        }
        return $row;
    }

    /**
     * Returns DB fields list
     *
     * @param UiComponentInterface $component
     * @return array
     */
    protected function getFields(UiComponentInterface $component)
    {
        $row = [];
        foreach ($this->getColumns($component) as $column) {
            if ($column->getData('config/label')) {
                $row[] = $column->getName();
            }
        }
        return $row;
    }

    /**
     * Returns row data
     *
     * @param DocumentInterface $document
     * @param array $fields
     * @return array
     */
    protected function getRowData(DocumentInterface $document, $fields)
    {
        $row = [];
        foreach ($fields as $column) {
            $row[] = $document->getCustomAttribute($column)->getValue();
        }
        return $row;
    }

    /**
     * Returns Filters with options
     *
     * @param UiComponentInterface $component
     * @return \Magento\Ui\Component\Filters
     * @throws \Exception
     */
    protected function getFieldOptions(UiComponentInterface $component)
    {
        $childComponents = $component->getChildComponents();
        $listingTop = $childComponents['listing_top'];
        foreach ($listingTop as $child) {
            if ($child instanceof \Magento\Ui\Component\Filters) {
                return $child;
            }
        }
        throw new \Exception('No filters found');
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCsvFile()
    {
        $component = $this->filter->getComponent();
        $name = md5(microtime());
        $file = 'export/'. $component->getName() . $name . '.csv';
        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $searchResult = $component->getContext()->getDataProvider()->getSearchResult();
        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');

        $fields = $this->getFields($component);
        $stream->lock();
        $stream->writeCsv($this->getHeaders($component));
        foreach ($searchResult->getItems() as $document) {
            $stream->writeCsv($this->getRowData($document, $fields));
        }
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true  // can delete file after use
        ];
    }
}
