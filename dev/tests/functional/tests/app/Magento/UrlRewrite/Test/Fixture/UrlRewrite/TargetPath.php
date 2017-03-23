<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\UrlRewrite\Test\Fixture\UrlRewrite;

use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Prepare Target Path.
 */
class TargetPath extends DataSource
{
    /**
     * Return entity.
     *
     * @var FixtureInterface
     */
    protected $entity = null;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param string $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = '')
    {
        $this->params = $params;
        if (!isset($data['entity']) || $data['entity'] === '-') {
            $this->data = $data;
            return;
        }
        preg_match('`%(.*?)%`', $data['entity'], $dataset);
        $entityConfig = isset($dataset[1]) ? explode('::', $dataset[1]) : [];
        if (count($entityConfig) > 1) {
            /** @var FixtureInterface $fixture */
            $this->entity = $fixtureFactory->createByCode($entityConfig[0], ['dataset' => $entityConfig[1]]);
            $this->entity->persist();
            $id = $this->entity->hasData('id') ? $this->entity->getId() : $this->entity->getPageId();
            $this->data = preg_replace('`(%.*?%)`', $id, $data['entity']);
        } else {
            $this->data = strval($data['entity']);
        }
    }

    /**
     * Return entity.
     *
     * @return FixtureInterface|null
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
