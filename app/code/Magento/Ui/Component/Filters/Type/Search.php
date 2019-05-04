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
class Search extends \Magento\Ui\Component\Filters\Type\AbstractFilter
{
    const NAME = 'keyword_search';

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
     * Transfer filters to dataProvider
     *
     * @return void
     */
    protected function applyFilter()
    {
        $value = $this->getContext()->getRequestParam('search');

        if ($value) {
            $filter = $this->filterBuilder->setConditionType('fulltext')
                ->setField($this->getName())
                ->setValue($value)
                ->create();

            $this->getContext()->getDataProvider()->addFilter($filter);
        }
    }
}
