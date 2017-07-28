<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Ui\Component\Listing\Columns;

/**
 * @api
 * @since 2.0.0
 */
class Listing extends AbstractComponent
{
    const NAME = 'listing';

    /**
     * @var array
     * @since 2.0.0
     */
    protected $columns = [];

    /**
     * Get component name
     *
     * @return string
     * @since 2.0.0
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getDataSourceData()
    {
        return ['data' => $this->getContext()->getDataProvider()->getData()];
    }
}
