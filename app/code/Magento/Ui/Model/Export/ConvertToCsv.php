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
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Ui\Component\Filters\Type\Select;

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
     * @param array $options
     * @return array
     */
    protected function getRowData(DocumentInterface $document, $fields, $options)
    {
        $row = [];
        foreach ($fields as $column) {
            if (isset($options[$column])) {
                $key = $document->getCustomAttribute($column)->getValue();
                if (isset($options[$column][$key])) {
                    $row[] = $options[$column][$key];
                }
                $row[] = '';
            } else {
                $row[] = $document->getCustomAttribute($column)->getValue();
            }
        }
        return $row;
    }

    /**
     * Returns complex option
     *
     * @param array $list
     * @param string $label
     * @param array $output
     * @return void
     */
    protected function getComplexLabel($list, $label, &$output)
    {
        foreach ($list as $item) {
            if (!is_array($item['value'])) {
                $output[$item['value']] = $label . $item['label'];
            } else {
                $this->getComplexLabel($item['value'], $label . $item['label'], $output);
            }
        }
    }

    /**
     * Returns array of Select options
     *
     * @param Select $filter
     * @return array
     */
    protected function getFilterOptions(Select $filter)
    {
        $options = [];
        foreach ($filter->getOptionProvider()->toOptionArray() as $option) {
            if (!is_array($option['value'])) {
                $options[$option['value']] = $option['label'];
            } else {
                $this->getComplexLabel(
                    $option['value'],
                    $option['label'],
                    $options
                );
            }
        }
        return $options;
    }
    /**
     * Returns Filters with options
     *
     * @return array
     */
    protected function getOptions()
    {
        $options = [];
        $component = $this->filter->getComponent();
        $childComponents = $component->getChildComponents();
        $listingTop = $childComponents['listing_top'];
        foreach ($listingTop->getChildComponents() as $child) {
            if ($child instanceof \Magento\Ui\Component\Filters) {
                foreach ($child->getChildComponents() as $filter) {
                    if ($filter instanceof Select) {
                        $options[$filter->getName()] = $this->getFilterOptions($filter);
                    }
                }
            }
        }
        return $options;
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCsvFile()
    {
        $options = $this->getOptions();
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
            $stream->writeCsv($this->getRowData($document, $fields, $options));
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
