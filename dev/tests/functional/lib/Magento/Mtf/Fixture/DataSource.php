<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Fixture;

use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Parent fixture data source class.
 */
class DataSource implements FixtureInterface
{
    /**
     * Data set configuration settings.
     *
     * @var array
     */
    protected $params;

    /**
     * Value data.
     *
     * @var array
     */
    protected $data;

    /**
     * Persist entity.
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set.
     *
     * @param string $key [optional]
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings.
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }
}
