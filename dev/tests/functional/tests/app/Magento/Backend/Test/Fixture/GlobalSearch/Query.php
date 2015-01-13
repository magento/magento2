<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Fixture\GlobalSearch;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\InjectableFixture;

/**
 * Class Query
 * Global Search query data provider
 */
class Query implements FixtureInterface
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
     * Data source entity
     *
     * @var InjectableFixture
     */
    protected $entity = null;

    /**
     * Constructor
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param string $data
     * @param array $params [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, $data, array $params = [])
    {
        $this->params = $params;
        $explodedData = explode('::', $data);
        switch (sizeof($explodedData)) {
            case 1:
                $this->data = $explodedData[0];
                break;
            case 3:
                list($fixture, $dataSet, $field) = $explodedData;
                $entity = $fixtureFactory->createByCode($fixture, ['dataSet' => $dataSet]);
                if (!$entity->hasData('id')) {
                    $entity->persist();
                }
                $this->data = $entity->getData($field);
                $this->entity = $entity;
                break;
            case 4:
                list($fixture, $dataSet, $source, $field) = $explodedData;
                $entity = $fixtureFactory->createByCode($fixture, ['dataSet' => $dataSet]);
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
     * Persist order products
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
     * Get entity for global search
     *
     * @return InjectableFixture
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Return data set configuration settings
     *
     * @return string
     */
    public function getDataConfig()
    {
        return $this->params;
    }
}
