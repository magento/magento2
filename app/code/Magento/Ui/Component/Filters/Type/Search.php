<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filters\Type;

/**
 * @api
 * @since 2.0.0
 */
class Search extends \Magento\Ui\Component\Filters\Type\AbstractFilter
{
    const NAME = 'keyword_search';

    /**
     * Prepare component configuration
     *
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
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
