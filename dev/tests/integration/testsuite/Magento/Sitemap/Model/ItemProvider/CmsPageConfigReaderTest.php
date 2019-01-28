<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sitemap\Model\ItemProvider;

use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;

class CmsPageConfigReaderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CategoryConfigReader
     */
    private $model = null;

    protected function setUp()
    {
        $this->model = Bootstrap::getObjectManager()->get(CmsPageConfigReader::class);
    }

    /**
     * @magentoConfigFixture default_store sitemap/page/changefreq monthly
     */
    public function testGetChangeFrequency()
    {
        $this->assertSame('daily', $this->model->getChangeFrequency(Store::DEFAULT_STORE_ID));
        $this->assertSame('monthly', $this->model->getChangeFrequency(Store::DISTRO_STORE_ID));
    }

    /**
     * @magentoConfigFixture default_store sitemap/page/priority 100
     */
    public function testGetCategoryPriority()
    {
        $this->assertSame(0.25, $this->model->getPriority(Store::DEFAULT_STORE_ID));
        $this->assertSame(100, $this->model->getPriority(Store::DISTRO_STORE_ID));
    }
}
