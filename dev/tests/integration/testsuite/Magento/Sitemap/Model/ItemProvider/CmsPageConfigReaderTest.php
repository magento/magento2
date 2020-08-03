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

    protected function setUp(): void
    {
        $this->model = Bootstrap::getObjectManager()->get(CmsPageConfigReader::class);
    }

    /**
     * @magentoConfigFixture default_store sitemap/page/changefreq monthly
     */
    public function testGetChangeFrequency()
    {
        $this->assertEquals('daily', $this->model->getChangeFrequency(Store::DEFAULT_STORE_ID));
        $this->assertEquals('monthly', $this->model->getChangeFrequency(Store::DISTRO_STORE_ID));
    }

    /**
     * @magentoConfigFixture default_store sitemap/page/priority 100
     */
    public function testGetCategoryPriority()
    {
        $this->assertEquals(0.25, $this->model->getPriority(Store::DEFAULT_STORE_ID));
        $this->assertEquals(100, $this->model->getPriority(Store::DISTRO_STORE_ID));
    }
}
