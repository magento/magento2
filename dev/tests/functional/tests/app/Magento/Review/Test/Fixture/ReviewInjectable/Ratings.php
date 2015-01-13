<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Fixture\ReviewInjectable;

use Magento\Review\Test\Fixture\Rating;
use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Ratings
 * Source for product ratings fixture
 */
class Ratings implements FixtureInterface
{
    /**
     * Configuration settings of fixture
     *
     * @var array
     */
    protected $params;

    /**
     * Data of the created ratings
     *
     * @var array
     */
    protected $data = [];

    /**
     * List of the created ratings
     *
     * @var array
     */
    protected $ratings = [];

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        /** @var Rating $fixtureRating */
        $fixtureRating = null;

        foreach ($data as $rating) {
            if (isset($rating['dataSet'])) {
                $fixtureRating = $fixtureFactory->createByCode('rating', ['dataSet' => $rating['dataSet']]);
                if (!$fixtureRating->hasData('rating_id')) {
                    $fixtureRating->persist();
                }
            } elseif (isset($rating['fixtureRating'])) {
                $fixtureRating = $rating['fixtureRating'];
            }

            if ($fixtureRating !== null) {
                $this->ratings[] = $fixtureRating;
                $this->data[] = [
                    'title' => $fixtureRating->getRatingCode(),
                    'rating' => $rating['rating'],
                ];
            }
        }
    }

    /**
     * Persist data
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
     * @return array
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

    /**
     * Get ratings
     *
     * @return array
     */
    public function getRatings()
    {
        return $this->ratings;
    }
}
