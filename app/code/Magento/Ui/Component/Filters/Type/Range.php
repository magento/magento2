<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filters\Type;

/**
 * @api
 * @since 100.0.2
 */
class Range extends AbstractFilter
{
    const NAME = 'filter_range';

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
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
        if (is_numeric($value)) {
            $filter = $this->filterBuilder->setConditionType($type)
                ->setField($this->getName())
                ->setValue($value)
                ->create();

            $this->getContext()->getDataProvider()->addFilter($filter);
        }
    }
}
