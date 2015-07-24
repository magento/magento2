<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Fixture\Widget;

use Magento\Cms\Test\Fixture\CmsPage;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;

/**
 * Prepare Cms page
 */
class PageIds implements FixtureInterface
{
    /**
     * Data set configuration settings
     *
     * @var array
     */
    protected $params = [];

    /**
     * Resource data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Return Cms page
     *
     * @var CmsPage
     */
    protected $cmsPage = [];

    /**
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if ($data['dataset']) {
            $dataset = explode(',', $data['dataset']);
            foreach ($dataset as $cmsPage) {
                /** @var CmsPage $cmsPage */
                $cmsPage = $fixtureFactory->createByCode('cmsPage', ['dataset' => $cmsPage]);
                if (!$cmsPage->getPageId()) {
                    $cmsPage->persist();
                }
                $this->cmsPage[] = $cmsPage;
                $this->data[] = $cmsPage->getPageId();
            }
        } else {
            $this->data[] = null;
        }
    }

    /**
     * Persist Cms page
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data
     *
     * @param string|null $key
     * @return string
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
     * Return Cms page
     *
     * @return CmsPage
     */
    public function getCmsPage()
    {
        return $this->cmsPage;
    }
}
