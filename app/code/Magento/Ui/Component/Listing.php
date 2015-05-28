<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\DataSourceInterface;

/**
 * Class Listing
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
        return [
            'data' => $this->getContext()->getDataProvider()->getData(),
            'totalCount' => $this->getContext()->getDataProvider()->count()
        ];
    }
}
