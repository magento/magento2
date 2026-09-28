<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

namespace Magento\Ui\Component\Filters\Type;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Filters\FilterModifier;
use Magento\Ui\Component\Form\Element\DataType\Date as DataTypeDate;

/**
 * Date grid filter UI Component
 *
 * @api
 * @since 100.0.2
 */
class Date extends AbstractFilter
{
    public const NAME = 'filter_date';

    public const COMPONENT = 'date';

    /**
     * @var DataTypeDate
     */
    protected $wrappedComponent;

    /**
     * @var string
     * @since 100.1.2
     */
    protected static $dateFormat = 'Y-m-d H:i:s';

    /**
     * @var bool
     */
    private bool $userDefined;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterBuilder $filterBuilder
     * @param FilterModifier $filterModifier
     * @param array $components
     * @param array $data
     * @param bool $userDefined
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterBuilder $filterBuilder,
        FilterModifier $filterModifier,
        array $components = [],
        array $data = [],
        bool $userDefined = false
    ) {
        $this->userDefined = $userDefined;
        parent::__construct($context, $uiComponentFactory, $filterBuilder, $filterModifier, $components, $data);
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        $this->wrappedComponent = $this->uiComponentFactory->create(
            $this->getName(),
            static::COMPONENT,
            ['context' => $this->getContext()]
        );
        $this->wrappedComponent->prepare();
        // Merge JS configuration with wrapped component configuration
        $jsConfig = array_replace_recursive(
            $this->getJsConfig($this->wrappedComponent),
            $this->getJsConfig($this)
        );
        $this->setData('js_config', $jsConfig);

        $this->setData(
            'config',
            array_replace_recursive(
                (array)$this->wrappedComponent->getData('config'),
                (array)$this->getData('config')
            )
        );

        $this->applyFilter();

        parent::prepare();
    }

    /**
     * Apply filter
     *
     * @return void
     */
    protected function applyFilter()
    {
        if (isset($this->filterData[$this->getName()])) {
            $value = $this->filterData[$this->getName()];

            if (empty($value)) {
                return;
            }

            if (is_array($value)) {
                if (isset($value['from'])) {
                    $this->applyFilterByType(
                        'gteq',
                        $this->convertDatetime((string)$value['from'])
                    );
                }

                if (isset($value['to'])) {
                    $this->applyFilterByType(
                        'lteq',
                        $this->convertDatetime((string)$value['to'], 23, 59, 59)
                    );
                }
            } else {
                $this->applyFilterByType('eq', $this->convertDatetime((string)$value));
            }
        }
    }

    /**
     * Apply filter by its type
     *
     * @param string $type
     * @param string $value
     * @return void
     */
    protected function applyFilterByType($type, $value)
    {
        if (!empty($value)) {
            $filter = $this->filterBuilder->setConditionType($type)
                ->setField($this->getName())
                ->setValue($value->format(static::$dateFormat))
                ->create();

            $this->getContext()->getDataProvider()->addFilter($filter);
        }
    }

    /**
     * Convert given date to default (UTC) timezone
     *
     * @param string $value
     * @param int $hour
     * @param int $minute
     * @param int $second
     * @return \DateTime|null
     */
    private function convertDatetime(string $value, int $hour = 0, int $minute = 0, int $second = 0): ?\DateTime
    {
        $value = $this->getData('config/options/showsTime')
            ? $this->wrappedComponent->convertDatetime(
                $value,
                !$this->getData('config/skipTimeZoneConversion')
            )
            : $this->wrappedComponent->convertDateWithTimezone(
                $value,
                $hour,
                $minute,
                $second,
                !$this->getData('config/skipTimeZoneConversion'),
                !$this->userDefined
            );

        return $value;
    }
}
