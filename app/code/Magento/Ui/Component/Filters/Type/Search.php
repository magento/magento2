<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Component\Filters\Type;

/**
 * Class Input
 */
class Search extends \Magento\Ui\Component\Filters\Type\AbstractFilter
{
    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return 'keyword_search';
    }

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
        $keyword = $this->getContext()->getRequestParam('search');
        if ($keyword) {
            $this->getContext()->getDataProvider()->addFilter($keyword, null, 'fulltext');
        }

    }
}
