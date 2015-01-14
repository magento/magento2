<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Fixture\GiftMessage;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Items
 * Prepare Items for GiftMessage
 */
class Items implements FixtureInterface
{
    /**
     * Prepared dataSet data
     *
     * @var array
     */
    protected $data;

    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * Constructor
     *
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['dataSets'])) {
            $dataSets = explode(',', $data['dataSets']);
            foreach ($dataSets as $dataSet) {
                $this->data[] = $fixtureFactory->createByCode('giftMessage', ['dataSet' => trim($dataSet)]);
            }
        } else {
            $this->data = $data;
        }
    }

    /**
     * Persist attribute options
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
     *
     * @param string|null $key [optional]
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }
}
