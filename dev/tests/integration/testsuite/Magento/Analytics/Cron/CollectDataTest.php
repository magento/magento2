<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks data collection process behaviour
 *
 * @see \Magento\Analytics\Cron\CollectData
 *
 * @magentoAppArea adminhtml
 */
class CollectDataTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CollectData */
    private $collectDataService;

    /** @var WriteInterface */
    private $mediaDirectory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->collectDataService = $this->objectManager->get(CollectData::class);
        $this->mediaDirectory = $this->objectManager->get(Filesystem::class)->getDirectoryWrite(DirectoryList::MEDIA);
        $this->removeAnalyticsDirectory();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->removeAnalyticsDirectory();

        parent::tearDown();
    }

    /**
     * @magentoConfigFixture current_store analytics/subscription/enabled 1
     * @magentoConfigFixture default/analytics/general/token 123
     *
     * @return void
     */
    public function testExecute(): void
    {
        $this->collectDataService->execute();
        $this->assertTrue(
            $this->mediaDirectory->isDirectory('analytics'),
            'Analytics was not created'
        );
        $files = $this->mediaDirectory->getDriver()
            ->readDirectoryRecursively($this->mediaDirectory->getAbsolutePath('analytics'));
        $file = array_filter($files, function ($element) {
            return substr($element, -8) === 'data.tgz';
        });
        $this->assertNotEmpty($file, 'File was not created');
    }

    /**
     * Remove Analytics directory
     *
     * @return void
     */
    private function removeAnalyticsDirectory(): void
    {
        $directoryToRemove = $this->mediaDirectory->getAbsolutePath('analytics');
        if ($this->mediaDirectory->isDirectory($directoryToRemove)) {
            $this->mediaDirectory->delete($directoryToRemove);
        }
    }
}
