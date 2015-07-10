<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filters\Type;

use Magento\Ui\Component\Form\Element\DataType\Date as DataTypeDate;

/**
 * Class DateRange
 */
class DateRange extends AbstractFilter
{
    const NAME = 'filter_range';

    const COMPONENT = 'date';

    /**
     * Wrapped component
     *
     * @var DataTypeDate
     */
    protected $wrappedComponent;

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
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

            if (isset($value['from'])) {
                $this->applyFilterByType('gteq', $value['from']);
            }

            if (isset($value['to'])) {
                $this->applyFilterByType('lteq', $value['to']);
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
            $value = $this->wrappedComponent->convertDate($value);

            $filter = $this->filterBuilder->setConditionType($type)
                ->setField($this->getName())
                ->setValue($value->format('Y-m-d H:i:s'))
                ->create();

            $this->getContext()->getDataProvider()->addFilter($filter);
        }
    }
}
