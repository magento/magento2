<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Composer\Test\Unit;

use Composer\Composer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Composer\ComposerFactory;
use Magento\Framework\Composer\ComposerJsonFinder;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ComposerFactoryTest extends TestCase
{
    /** @var string Test COMPOSER_HOME environment variable value */
    private $testComposerHome = __DIR__ . '/_files/composer_home';

    /** @var string Location of test fixtures */
    private $fixturesDir = __DIR__ . '/_files/';

    /** @var string Original value of COMPOSER_HOME environment variable */
    private $originalComposerHome;

    /** @var File */
    private $fileDriver;

    protected function setUp(): void
    {
        $this->fileDriver = new File();
        $this->originalComposerHome = getenv('COMPOSER_HOME');
        putenv('COMPOSER_HOME');
    }

    protected function tearDown(): void
    {
        if ($this->originalComposerHome) {
            putenv('COMPOSER_HOME=' . $this->originalComposerHome);
        }

        // Composer home directory is created one level up from where composer.json is.
        if (is_dir($this->testComposerHome)) {
            $this->fileDriver->deleteDirectory($this->testComposerHome);
        }
    }

    public function testCreate()
    {
        $objectManager = new ObjectManager($this);
        $dirListMock = $this->getMockBuilder(DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $composerJsonFinderMock = $this->getMockBuilder(ComposerJsonFinder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $composerJsonPath = $this->fixturesDir . 'composer.json';

        $dirListMock->expects($this->once())
            ->method('getPath')
            ->willReturn($this->testComposerHome);
        $composerJsonFinderMock->expects($this->once())
            ->method('findComposerJson')
            ->willReturn($composerJsonPath);

        /** @var ComposerFactory $factory */
        $factory = $objectManager->getObject(
            ComposerFactory::class,
            [
                'directoryList' => $dirListMock,
                'composerJsonFinder' => $composerJsonFinderMock
            ]
        );

        $this->assertInstanceOf(Composer::class, $factory->create());
    }
}
