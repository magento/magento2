<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Model\Export;

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Filters;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Metadata Provider for grid listing export.
 *
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
        $dateFormat = 'M j, Y h:i:s A',
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
     *
     * @return UiComponentInterface
     * @throws Exception
     */
    protected function getColumnsComponent(UiComponentInterface $component): UiComponentInterface
    {
        foreach ($component->getChildComponents() as $childComponent) {
            if ($childComponent instanceof Columns) {
                return $childComponent;
            }
        }
        throw new Exception('No columns found'); // @codingStandardsIgnoreLine
    }

    /**
     * Returns columns list
     *
     * @param UiComponentInterface $component
     *
     * @return UiComponentInterface[]
     * @throws Exception
     */
    protected function getColumns(UiComponentInterface $component): array
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
     *
     * @return string[]
     * @throws Exception
     */
    public function getHeaders(UiComponentInterface $component): array
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
     *
     * @return string[]
     * @throws Exception
     */
    public function getFields(UiComponentInterface $component): array
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
     *
     * @return string[]
     */
    public function getRowData(DocumentInterface $document, $fields, $options): array
    {
        $row = [];
        foreach ($fields as $column) {
            if (isset($options[$column])) {
                $key = $document->getCustomAttribute($column)->getValue();
                if (isset($options[$column][$key])) {
                    $row[] = $options[$column][$key];
                } else {
                    $row[] = $key;
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
     *
     * @return void
     */
    protected function getComplexLabel($list, $label, &$output): void
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
     * Prepare array of options.
     *
     * @param array $options
     *
     * @return array
     */
    protected function getOptionsArray(array $options): array
    {
        $preparedOptions = [];
        foreach ($options as $option) {
            if (!is_array($option['value'])) {
                $preparedOptions[$option['value']] = $option['label'];
            } else {
                $this->getComplexLabel(
                    $option['value'],
                    $option['label'],
                    $preparedOptions
                );
            }
        }

        return $preparedOptions;
    }

    /**
     * Returns Filters with options
     *
     * @return array
     * @throws LocalizedException
     */
    public function getOptions(): array
    {
        return array_merge(
            $this->getColumnOptions(),
            $this->getFilterOptions()
        );
    }

    /**
     * Get options from columns.
     *
     * @return array
     * @throws LocalizedException
     * @throws Exception
     */
    protected function getColumnOptions(): array
    {
        $options = [];
        $component = $this->filter->getComponent();
        /** @var Column $columnComponent */
        foreach ($this->getColumns($component) as $columnComponent) {
            if ($columnComponent->hasData('options')) {
                $optionSource = $columnComponent->getData('options');
                $optionsArray = $optionSource instanceof OptionSourceInterface ?
                    $optionSource->toOptionArray() : $optionSource;
                $options[$columnComponent->getName()] = $this->getOptionsArray($optionsArray ?: []);
            }
        }

        return $options;
    }

    /**
     * Get options from column filters.
     *
     * @return array
     * @throws LocalizedException
     */
    protected function getFilterOptions(): array
    {
        $options = [];
        $component = $this->filter->getComponent();
        $childComponents = $component->getChildComponents();
        $listingTop = $childComponents['listing_top'];
        foreach ($listingTop->getChildComponents() as $child) {
            if ($child instanceof Filters) {
                foreach ($child->getChildComponents() as $filter) {
                    if ($filter instanceof Select) {
                        $options[$filter->getName()] = $this->getOptionsArray($filter->getData('config/options'));
                    }
                }
            }
        }

        return $options;
    }

    /**
     * Convert document date(UTC) fields to default scope specified
     *
     * @param DocumentInterface $document
     * @param string $componentName
     *
     * @return void
     * @throws Exception
     */
    public function convertDate($document, $componentName): void
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
                new DateTime($fieldValue, new DateTimeZone('UTC')),
                $this->locale,
                true
            );
            $document->setData($field, $convertedDate->format($this->dateFormat));
        }
    }
}
