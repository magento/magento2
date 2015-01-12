<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class Sitemap
 *
 */
class Sitemap extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Sitemap\Test\Repository\Sitemap';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Sitemap\Test\Handler\Sitemap\SitemapInterface';

    protected $defaultDataSet = [
        'sitemap_filename' => 'sitemap.xml',
        'sitemap_path' => '/',
    ];

    protected $sitemap_id = [
        'attribute_code' => 'sitemap_id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $sitemap_type = [
        'attribute_code' => 'sitemap_type',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $sitemap_filename = [
        'attribute_code' => 'sitemap_filename',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $sitemap_path = [
        'attribute_code' => 'sitemap_path',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $sitemap_time = [
        'attribute_code' => 'sitemap_time',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $store_id = [
        'attribute_code' => 'store_id',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    public function getSitemapId()
    {
        return $this->getData('sitemap_id');
    }

    public function getSitemapType()
    {
        return $this->getData('sitemap_type');
    }

    public function getSitemapFilename()
    {
        return $this->getData('sitemap_filename');
    }

    public function getSitemapPath()
    {
        return $this->getData('sitemap_path');
    }

    public function getSitemapTime()
    {
        return $this->getData('sitemap_time');
    }

    public function getStoreId()
    {
        return $this->getData('store_id');
    }
}
