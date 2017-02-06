<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Test\Fixture\StoreGroup;

use Magento\Mtf\Fixture\DataSource;
use Magento\Store\Test\Fixture\Website;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Prepare WebsiteId for Store Group.
 */
class WebsiteId extends DataSource
{
    /**
     * Website fixture.
     *
     * @var Website
     */
    protected $website;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['fixture'])) {
            $this->website = $data['fixture'];
            $this->data = $this->website->getName();
        } elseif (isset($data['dataset'])) {
            $website = $fixtureFactory->createByCode('website', ['dataset' => $data['dataset']]);
            /** @var Website $website */
            if (!$website->getWebsiteId()) {
                $website->persist();
            }
            $this->website = $website;
            $this->data = $website->getName();
        }
    }

    /**
     * Return Website fixture
     *
     * @return Website
     */
    public function getWebsite()
    {
        return $this->website;
    }
}
