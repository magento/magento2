<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Fixture\Category;

use Magento\Cms\Test\Fixture\CmsBlock;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Prepare landing page.
 */
class LandingPage implements FixtureInterface
{
    /**
     * Prepared dataSet data.
     *
     * @var string
     */
    protected $data;

    /**
     * Source Cms Block.
     *
     * @var CmsBlock
     */
    protected $cmsBlock = null;

    /**
     * Fixture params.
     *
     * @var array
     */
    protected $params;

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

        if (isset($data['preset'])) {
            /** @var CmsBlock $cmsBlock */
            $cmsBlock = $fixtureFactory->createByCode('cmsBlock', ['dataSet' => $data['preset']]);
            if (!$cmsBlock->getBlockId()) {
                $cmsBlock->persist();
            }

            $this->data = $cmsBlock->getTitle();
            $this->cmsBlock = $cmsBlock;
        }
    }

    /**
     * Persist source.
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set.
     *
     * @param string|null $key [optional]
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings.
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
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
