<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Fixture\Review;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Source for entity id fixture.
 */
class EntityId extends DataSource
{
    /**
     * The created entity.
     *
     * @var FixtureInterface
     */
    protected $entity = null;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;

        if (isset($data['dataset'])) {
            list($typeFixture, $dataset) = explode('::', $data['dataset']);
            $fixture = $fixtureFactory->createByCode($typeFixture, ['dataset' => $dataset]);
            if (!$fixture->hasData('id')) {
                $fixture->persist();
            }

            $this->entity = $fixture;
            $this->data = $fixture->getId();
        }
    }

    /**
     * Get entity.
     *
     * @return FixtureInterface|null
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
