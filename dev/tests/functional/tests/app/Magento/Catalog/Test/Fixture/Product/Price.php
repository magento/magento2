<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Product;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Repository\RepositoryFactory;

/**
 * Price data source.
 *
 * Data keys:
 *  - dataset (Price verification dataset name)
 *  - value (Price value)
 */
class Price extends DataSource
{
    /**
     * Price view on different pages.
     *
     * @var string
     */
    protected $priceData = null;

    /**
     * @constructor
     * @param RepositoryFactory $repositoryFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(RepositoryFactory $repositoryFactory, array $params, $data = [])
    {
        $this->params = $params;
        $this->data = (!isset($data['dataset']) && !isset($data['value'])) ? $data : null;

        if (isset($data['value'])) {
            $this->data = $data['value'];
        }

        if (isset($data['dataset']) && isset($this->params['repository'])) {
            $this->priceData = $repositoryFactory->get($this->params['repository'])->get($data['dataset']);
        }
    }

    /**
     * Get price data for different pages.
     *
     * @return array|null
     */
    public function getPriceData()
    {
        return $this->priceData;
    }
}
