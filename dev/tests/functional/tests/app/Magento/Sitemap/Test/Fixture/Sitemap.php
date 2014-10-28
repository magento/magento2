<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        'sitemap_path' => '/'
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
