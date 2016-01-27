<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Fixture\Widget;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Repository\RepositoryFactory;

/**
 * Prepare Widget options for widget.
 */
class Parameters extends DataSource
{
    /**
     * Widget option entities.
     *
     * @var array
     */
    protected $entities;

    /**
     * @constructor
     * @param RepositoryFactory $repositoryFactory
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(
        RepositoryFactory $repositoryFactory,
        FixtureFactory $fixtureFactory,
        array $params,
        array $data = []
    ) {
        $this->params = $params;
        if (isset($data['dataset']) && isset($this->params['repository'])) {
            $this->data = $repositoryFactory->get($this->params['repository'])->get($data['dataset']);
            if (isset($this->data['entities'])) {
                foreach ($this->data['entities'] as $index => $entity) {
                    $explodeValue = explode('::', $entity);
                    $fixture = $fixtureFactory->createByCode($explodeValue[0], ['dataset' => $explodeValue[1]]);
                    $fixture->persist();
                    $this->data['entities'][$index] = $fixture;
                    $this->entities[] = $fixture;
                }
            }
        } elseif (isset($data['entity']) && $data['entity'] instanceof FixtureInterface) {
            $this->data['entities'][] = $data['entity'];
        } else {
            $this->data = $data;
        }
    }

    /**
     * Return entities.
     *
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }
}
