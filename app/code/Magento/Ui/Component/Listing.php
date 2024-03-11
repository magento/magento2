<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Ui\Component\Listing\Columns;

/**
 * @api
 * @since 100.0.2
 */
class Listing extends AbstractComponent
{
    const NAME = 'listing';

    /**
     * @var array
     */
    protected $columns = [];

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
     * {@inheritdoc}
     */
    public function getDataSourceData()
    {
        // Only load data in an ajax (json) request for better performance
        if ($this->getContext()->getAcceptType() == 'json') {
            return ['data' => $this->getContext()->getDataProvider()->getData()];
        }

        // build out default array keys to revent null erros, in case some module expects this
        return ['data' => ['items' => [], 'totalRecords' => 0]];
    }
}
