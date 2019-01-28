<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Fixture\GlobalSearch;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\InjectableFixture;

/**
 * Global Search query data provider.
 */
class Query extends DataSource
{
    /**
     * Data source entity.
     *
     * @var InjectableFixture
     */
    protected $entity = null;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param string $data
     * @param array $params [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, $data, array $params = [])
    {
        $this->params = $params;
        $explodedData = explode('::', $data);
        switch (count($explodedData)) {
            case 1:
                $this->data = $explodedData[0];
                break;
            case 3:
                list($fixture, $dataset, $field) = $explodedData;
                $entity = $fixtureFactory->createByCode($fixture, ['dataset' => $dataset]);
                if (!$entity->hasData('id')) {
                    $entity->persist();
                }
                $this->data = $entity->getData($field);
                $this->entity = $entity;
                break;
            case 4:
                list($fixture, $dataset, $source, $field) = $explodedData;
                $entity = $fixtureFactory->createByCode($fixture, ['dataset' => $dataset]);
                if (!$entity->hasData('id')) {
                    $entity->persist();
                }
                $source = $source == 'product' ? $entity->getEntityId()['products'][0] : $entity->getData($source);
                $this->data = $source->getData($field);
                $this->entity = $entity;
                break;
        }
    }

    /**
     * Get entity for global search.
     *
     * @return InjectableFixture
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
