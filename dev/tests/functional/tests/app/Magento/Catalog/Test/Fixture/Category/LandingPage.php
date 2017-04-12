<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Category;

use Magento\Mtf\Fixture\DataSource;
use Magento\Cms\Test\Fixture\CmsBlock;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Prepare landing page.
 */
class LandingPage extends DataSource
{
    /**
     * Source Cms Block.
     *
     * @var CmsBlock
     */
    protected $cmsBlock = null;

    /**
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data [optional]
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, $data = [])
    {
        $this->params = $params;
        $this->data = $data;

        if (isset($data['dataset'])) {
            /** @var CmsBlock $cmsBlock */
            $cmsBlock = $fixtureFactory->createByCode('cmsBlock', ['dataset' => $data['dataset']]);
            if (!$cmsBlock->getBlockId()) {
                $cmsBlock->persist();
            }

            $this->data = $cmsBlock->getTitle();
            $this->cmsBlock = $cmsBlock;
        }
    }

    /**
     * Return Cms Block.
     *
     * @return CmsBlock
     */
    public function getCmsBlock()
    {
        return $this->cmsBlock;
    }
}
