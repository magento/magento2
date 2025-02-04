<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Ui\Component;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Listing\Columns\ColumnInterface;

/**
 * Create columns factory on product grid page
 *
 * @api
 * @since 100.0.2
 */
class ColumnFactory
{
    /**
     * @var UiComponentFactory
     */
    protected $componentFactory;

    /**
     * @var array
     */
    protected $jsComponentMap = [
        'text' => 'Magento_Ui/js/grid/columns/column',
        'select' => 'Magento_Ui/js/grid/columns/select',
        'multiselect' => 'Magento_Ui/js/grid/columns/select',
        'date' => 'Magento_Ui/js/grid/columns/date',
    ];

    /**
     * @var array
     */
    protected $dataTypeMap = [
        'default' => 'text',
        'text' => 'text',
        'boolean' => 'select',
        'select' => 'select',
        'multiselect' => 'multiselect',
        'date' => 'date',
        'datetime' => 'date',
    ];

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @param UiComponentFactory $componentFactory
     * @param TimezoneInterface|null $timezone
     */
    public function __construct(
        UiComponentFactory $componentFactory,
        ?TimezoneInterface $timezone = null
    ) {
        $this->componentFactory = $componentFactory;
        $this->timezone = $timezone
            ?? ObjectManager::getInstance()->get(TimezoneInterface::class);
    }

    /**
     * Create Factory
     *
     * @param ProductAttributeInterface $attribute
     * @param ContextInterface $context
     * @param array $config
     *
     * @return ColumnInterface
     * @throws LocalizedException
     */
    public function create($attribute, $context, array $config = [])
    {
        $filterModifiers = $context->getRequestParam(FilterModifier::FILTER_MODIFIER, []);

        $columnName = $attribute->getAttributeCode();
        $config = array_merge(
            [
                'label' => __($attribute->getDefaultFrontendLabel()),
                'dataType' => $this->getDataType($attribute),
                'add_field' => true,
                'visible' => $attribute->getIsVisibleInGrid(),
                'filter' => ($attribute->getIsFilterableInGrid() || array_key_exists($columnName, $filterModifiers))
                    ? $this->getFilterType($attribute->getFrontendInput())
                    : null,
            ],
            $config
        );

        if ($attribute->usesSource()) {
            $config['options'] = $attribute->getSource()->getAllOptions(true, true);
            foreach ($config['options'] as &$optionData) {
                $optionData['__disableTmpl'] = true;
            }
        }

        $config['component'] = $this->getJsComponent($config['dataType']);

        if ($config['dataType'] === 'date') {
            $config += $this->getDateConfig($attribute);
        }

        $arguments = [
            'data' => [
                'config' => $config,
            ],
            'context' => $context,
        ];

        return $this->componentFactory->create($columnName, 'column', $arguments);
    }

    /**
     * Get config for Date columns
     *
     * @param ProductAttributeInterface $attribute
     * @return array
     */
    private function getDateConfig(ProductAttributeInterface $attribute): array
    {
        $isDatetime = $attribute->getFrontendInput() === 'datetime';
        $dateFormat = $isDatetime
            ? $this->timezone->getDateTimeFormat(\IntlDateFormatter::MEDIUM)
            : $this->timezone->getDateFormat(\IntlDateFormatter::MEDIUM);
        $timezone = $isDatetime
            ? $this->timezone->getConfigTimezone()
            : $this->timezone->getDefaultTimezone();

        return [
            'timezone' => $timezone,
            'dateFormat' => $dateFormat,
            'options' => ['showsTime' => $isDatetime],
        ];
    }

    /**
     * Get Js Component
     *
     * @param string $dataType
     *
     * @return string
     */
    protected function getJsComponent($dataType)
    {
        return $this->jsComponentMap[$dataType];
    }

    /**
     * Get Data Type
     *
     * @param ProductAttributeInterface $attribute
     *
     * @return string
     */
    protected function getDataType($attribute)
    {
        return $this->dataTypeMap[$attribute->getFrontendInput()] ?? $this->dataTypeMap['default'];
    }

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $frontendInput
     * @return string
     */
    protected function getFilterType($frontendInput)
    {
        $filtersMap = ['date' => 'dateRange', 'datetime' => 'dateRange'];
        $result = array_replace_recursive($this->dataTypeMap, $filtersMap);

        return $result[$frontendInput] ?? $result['default'];
    }
}
