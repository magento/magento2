<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sitemap\Model;

use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sitemap\Model\Sitemap;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Request;
use Zend\Stdlib\Parameters;
use PHPUnit\Framework\TestCase;

class SitemapTest extends TestCase
{
    /**
     * @var Sitemap
     */
    private $model;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(Sitemap::class);
    }

    /**
     * Test get sitemap URL from parent root directory path
     *
     * @return void
     */
    public function testGetSitemapUrlFromParentRootDirectoryPath(): void
    {
        /** @var Value $configValue */
        $configValue = $this->objectManager->get(Value::class);
        $configValue->load('web/unsecure/base_url', 'path');
        $baseUrl = $configValue->getValue() ?: 'http://localhost/';

        /** @var Filesystem $filesystem */
        $filesystem = $this->objectManager->create(Filesystem::class);
        $rootDir = $filesystem->getDirectoryRead(DirectoryList::ROOT)
            ->getAbsolutePath();
        $requestPath = dirname($rootDir);

        /** @var Request $request */
        $request = $this->objectManager->get(Request::class);
        //imitation run script from parent root directory
        $request->setServer(new Parameters(['DOCUMENT_ROOT' => $requestPath]));

        $sitemapUrl = $this->model->getSitemapUrl('/', 'sitemap.xml');

        $this->assertEquals($baseUrl.'sitemap.xml', $sitemapUrl);
    }
}
