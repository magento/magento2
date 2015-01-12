<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Fixture\ReviewInjectable;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;

/**
 * Class EntityId
 * Source for entity id fixture
 */
class EntityId extends InjectableFixture
{
    /**
     * Configuration settings
     *
     * @var array
     */
    protected $params;

    /**
     * Id of the created entity
     *
     * @var int
     */
    protected $data = null;

    /**
     * The created entity
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

        if (isset($data['dataSet'])) {
            list($typeFixture, $dataSet) = explode('::', $data['dataSet']);
            $fixture = $fixtureFactory->createByCode($typeFixture, ['dataSet' => $dataSet]);
            if (!$fixture->hasData('id')) {
                $fixture->persist();
            }

            $this->entity = $fixture;
            $this->data = $fixture->getId();
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
     * Return id of the created entity
     *
     * @param string|null $key [optional]
     * @return int
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
     * Get entity
     *
     * @return FixtureInterface|null
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
