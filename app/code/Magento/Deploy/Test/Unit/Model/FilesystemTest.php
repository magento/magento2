<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Deploy\Test\Unit\Model;

use Magento\Deploy\Model\Filesystem as DeployFilesystem;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Setup\Lists;
use Magento\Framework\ShellInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Validator\Locale;
use Magento\Store\Model\Config\StoreView;
use Magento\User\Model\ResourceModel\User\Collection;
use Magento\User\Model\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilesystemTest extends TestCase
{
    /**
     * @var StoreView|MockObject
     */
    private $storeView;

    /**
     * @var ShellInterface|MockObject
     */
    private $shell;

    /**
     * @var OutputInterface|MockObject
     */
    private $output;

    /**
     * @var Filesystem|MockObject
     */
    private $filesystem;

    /**
     * @var WriteInterface|MockObject
     */
    private $directoryWrite;

    /**
     * @var Collection|MockObject
     */
    private $userCollection;

    /**
     * @var ObjectManagerInterface|MockObject
     */
    private $objectManager;

    /**
     * @var DeployFilesystem
     */
    private $deployFilesystem;

    /**
     * @var string
     */
    private $cmdPrefix;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->storeView = $this->getMockBuilder(StoreView::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shell = $this->getMockBuilder(ShellInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->output = $this->getMockBuilder(OutputInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryWrite = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->filesystem->method('getDirectoryWrite')
            ->willReturn($this->directoryWrite);

        $this->userCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lists = $this->getMockBuilder(Lists::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lists->method('getLocaleList')
            ->willReturn([
                'fr_FR' => 'France',
                'de_DE' => 'Germany',
                'nl_NL' => 'Netherlands',
                'en_US' => 'USA'
            ]);
        $locale = $objectManager->getObject(Locale::class, ['lists' => $lists]);

        $this->deployFilesystem = $objectManager->getObject(
            DeployFilesystem::class,
            [
                'storeView' => $this->storeView,
                'shell' => $this->shell,
                'filesystem' => $this->filesystem,
                'userCollection' => $this->userCollection,
                'locale' => $locale
            ]
        );

        $this->cmdPrefix = PHP_BINARY . ' -f ' . BP . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'magento ';
    }

    /**
     * @return void
     */
    public function testRegenerateStatic(): void
    {
        $storeLocales = ['fr_FR', 'de_DE', 'nl_NL'];
        $this->storeView->method('retrieveLocales')
            ->willReturn($storeLocales);

        $setupDiCompileCmd = $this->cmdPrefix . 'setup:di:compile';
        $this->initAdminLocaleMock('en_US');

        $usedLocales = ['fr_FR', 'de_DE', 'nl_NL', 'en_US'];
        $cacheFlushCmd = $this->cmdPrefix . 'cache:flush';
        $staticContentDeployCmd = $this->cmdPrefix . 'setup:static-content:deploy -f '
            . implode(' ', $usedLocales);
        $this->shell
            ->expects($this->exactly(4))
            ->method('execute')
            ->withConsecutive([$cacheFlushCmd], [$setupDiCompileCmd], [$cacheFlushCmd], [$staticContentDeployCmd]);

        $this->output
            ->method('writeln')
            ->withConsecutive(
                ['Starting compilation'],
                [],
                ['Compilation complete'],
                ['Starting deployment of static content'],
                [],
                ['Deployment of static content complete']
            );

        $this->deployFilesystem->regenerateStatic($this->output);
    }

    /**
     * Checks a case when configuration contains incorrect locale code.
     *
     * @return void
     */
    public function testGenerateStaticForNotAllowedStoreViewLocale(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            ';echo argument has invalid value, run info:language:list for list of available locales'
        );
        $storeLocales = ['fr_FR', 'de_DE', ';echo'];
        $this->storeView->method('retrieveLocales')
            ->willReturn($storeLocales);

        $this->initAdminLocaleMock('en_US');

        $this->deployFilesystem->regenerateStatic($this->output);
    }

    /**
     * Checks as case when admin locale is incorrect.
     *
     * @return void
     */
    public function testGenerateStaticForNotAllowedAdminLocale(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage(
            ';echo argument has invalid value, run info:language:list for list of available locales'
        );
        $storeLocales = ['fr_FR', 'de_DE', 'en_US'];
        $this->storeView->method('retrieveLocales')
            ->willReturn($storeLocales);

        $this->initAdminLocaleMock(';echo');

        $this->deployFilesystem->regenerateStatic($this->output);
    }

    /**
     * Initializes admin user locale.
     *
     * @param string $locale
     *
     * @return void
     */
    private function initAdminLocaleMock($locale): void
    {
        /** @var User|MockObject $user */
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
        $user->method('getInterfaceLocale')
            ->willReturn($locale);
        $this->userCollection->method('getIterator')
            ->willReturn(new \ArrayIterator([$user]));
    }
}
