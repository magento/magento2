<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CmsUrlRewrite\Plugin\Cms\Model\Store;

use Magento\Store\Model\StoreFactory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Test for plugin which is listening store resource model and on save replace cms page url rewrites
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->storeFactory = Bootstrap::getObjectManager()->create(StoreFactory::class);
        $this->urlFinder = Bootstrap::getObjectManager()->create(UrlFinderInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Cms/_files/pages.php
     * @magentoDataFixture Magento/Store/_files/store.php
     * @magentoAppArea adminhtml
     */
    public function testAfterSave()
    {
        $data = [
            UrlRewrite::REQUEST_PATH => 'page100',
        ];
        $urlRewrites = $this->urlFinder->findAllByData($data);
        $this->assertCount(2, $urlRewrites);
    }
}
