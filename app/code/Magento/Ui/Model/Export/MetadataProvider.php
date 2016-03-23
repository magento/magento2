<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Model\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Filters;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MetadataProvider
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $dateFormat;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param Filter $filter
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     * @param string $dateFormat
     * @param array $data
     */
    public function __construct(
        Filter $filter,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        $dateFormat = 'M j, Y H:i:s A',
        array $data = []
    ) {
        $this->filter = $filter;
        $this->localeDate = $localeDate;
        $this->locale = $localeResolver->getLocale();
        $this->dateFormat = $dateFormat;
        $this->data = $data;
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
            if ($childComponent instanceof Columns) {
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
                if ($column->getData('config/label') && $column->getData('config/dataType') !== 'actions') {
                    $this->columns[$component->getName()][$column->getName()] = $column;
                }
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
    public function getHeaders(UiComponentInterface $component)
    {
        $row = [];
        foreach ($this->getColumns($component) as $column) {
            $row[] = $column->getData('config/label');
        }
        return $row;
    }

    /**
     * Returns DB fields list
     *
     * @param UiComponentInterface $component
     * @return array
     */
    public function getFields(UiComponentInterface $component)
    {
        $row = [];
        foreach ($this->getColumns($component) as $column) {
            $row[] = $column->getName();
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
    public function getRowData(DocumentInterface $document, $fields, $options)
    {
        $row = [];
        foreach ($fields as $column) {
            if (isset($options[$column])) {
                $key = $document->getCustomAttribute($column)->getValue();
                if (isset($options[$column][$key])) {
                    $row[] = $options[$column][$key];
                } else {
                    $row[] = '';
                }
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
        foreach ($filter->getData('config/options') as $option) {
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
    public function getOptions()
    {
        $options = [];
        $component = $this->filter->getComponent();
        $childComponents = $component->getChildComponents();
        $listingTop = $childComponents['listing_top'];
        foreach ($listingTop->getChildComponents() as $child) {
            if ($child instanceof Filters) {
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
     * Convert document date(UTC) fields to default scope specified
     *
     * @param \Magento\Framework\Api\Search\DocumentInterface $document
     * @param string $componentName
     * @return void
     */
    public function convertDate($document, $componentName)
    {
        if (!isset($this->data[$componentName])) {
            return;
        }
        foreach ($this->data[$componentName] as $field) {
            $fieldValue = $document->getData($field);
            if (!$fieldValue) {
                continue;
            }
            $convertedDate = $this->localeDate->date(
                new \DateTime($fieldValue, new \DateTimeZone('UTC')),
                $this->locale,
                true
            );
            $document->setData($field, $convertedDate->format($this->dateFormat));
        }
    }
}
