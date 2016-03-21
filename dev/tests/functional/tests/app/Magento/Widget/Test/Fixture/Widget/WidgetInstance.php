<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Fixture\Widget;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\DataSource;
use Magento\Mtf\Repository\RepositoryFactory;

/**
 * Prepare Widget instances (layouts) for widget.
 */
class WidgetInstance extends DataSource
{
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
            foreach ($this->data as $index => $layouts) {
                if (isset($layouts['entities'])) {
                    $explodeValue = explode('::', $layouts['entities']);
                    $fixture = $fixtureFactory->createByCode($explodeValue[0], ['dataset' => $explodeValue[1]]);
                    $fixture->persist();
                    $this->data[$index]['entities'] = $fixture;
                }
            }
        } else {
            $this->data = $data;
        }
    }
}
