<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filters\Type;

use Magento\Ui\Component\Form\Element\DataType\Date as DataTypeDate;

/**
 * Class DateRange
 * @api
 * @since 2.0.0
 */
class DateRange extends Range
{
    const COMPONENT = 'date';

    /**
     * Wrapped component
     *
     * @var DataTypeDate
     * @since 2.0.0
     */
    protected $wrappedComponent;

    /**
     * Prepare component configuration
     *
     * @return void
     * @since 2.0.0
     */
    public function prepare()
    {
        $this->wrappedComponent = $this->uiComponentFactory->create(
            $this->getName(),
            static::COMPONENT,
            ['context' => $this->getContext()]
        );

        $this->wrappedComponent->prepare();

        parent::prepare();
    }

    /**
     * Apply filter by its type
     *
     * @param string $type
     * @param string $value
     * @return void
     * @since 2.0.0
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
