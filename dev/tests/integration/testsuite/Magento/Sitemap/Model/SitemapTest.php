<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sitemap\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\ObjectManagerInterface;
use Magento\Sitemap\Model\Sitemap;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Request;
use Zend\Stdlib\Parameters;
use PHPUnit\Framework\TestCase;

/**
 * Tests \Magento\Sitemap\Model\Sitemap functionality.
 */
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
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->model = $this->objectManager->get(Sitemap::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
    }

    /**
     * Test get sitemap URL from parent root directory path
     *
     * @return void
     */
    public function testGetSitemapUrlFromParentRootDirectoryPath(): void
    {
        $rootDir = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)
            ->getAbsolutePath();
        $requestPath = dirname($rootDir);

        /** @var Request $request */
        $request = $this->objectManager->get(Request::class);
        //imitation run script from parent root directory
        $request->setServer(new Parameters(['DOCUMENT_ROOT' => $requestPath]));

        $sitemapUrl = $this->model->getSitemapUrl('/', 'sitemap.xml');

        $this->assertEquals('http://localhost/sitemap.xml', $sitemapUrl);
    }
}
