<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Fixture\Review;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Review\Test\Fixture\Rating;

/**
 * Source for product ratings fixture.
 */
class Ratings extends DataSource
{
    /**
     * List of the created ratings.
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
            if (isset($rating['dataset'])) {
                $fixtureRating = $fixtureFactory->createByCode('rating', ['dataset' => $rating['dataset']]);
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
                    'rating' => $rating['rating']
                ];
            }
        }
    }

    /**
     * Get ratings.
     *
     * @return array
     */
    public function getRatings()
    {
        return $this->ratings;
    }
}
