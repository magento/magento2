<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model;

use Magento\Deploy\Model\Filesystem as DeployFilesystem;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\ShellInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Config\StoreView;
use Magento\User\Model\ResourceModel\User\Collection;
use Magento\User\Model\User;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\Validator\Locale;
use Magento\Framework\Setup\Lists;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilesystemTest extends \PHPUnit\Framework\TestCase
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
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);

<<<<<<< HEAD
        $this->storeView = $this->createMock(StoreView::class);
        $this->shell = $this->createMock(ShellInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
        $this->objectManager = $this->createMock(ObjectManagerInterface::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->directoryWrite = $this->createMock(WriteInterface::class);
        $this->filesystem->method('getDirectoryWrite')
            ->willReturn($this->directoryWrite);

        $this->userCollection = $this->createMock(Collection::class);

        $lists = $this->getMockBuilder(Lists::class)
            ->disableOriginalConstructor()
            ->getMock();
=======
        $this->storeView = $this->getMockBuilder(StoreView::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->shell = $this->getMockBuilder(ShellInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->output = $this->getMockBuilder(OutputInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManager = $this->getMockBuilder(ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryWrite = $this->getMockBuilder(WriteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filesystem->method('getDirectoryWrite')
            ->willReturn($this->directoryWrite);

        $this->userCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $lists = $this->getMockBuilder(Lists::class)
            ->disableOriginalConstructor()
            ->getMock();

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $lists->method('getLocaleList')
            ->willReturn([
                'fr_FR' => 'France',
                'de_DE' => 'Germany',
                'nl_NL' => 'Netherlands',
<<<<<<< HEAD
                'en_US' => 'USA'
=======
                'en_US' => 'USA',
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ]);
        $locale = $objectManager->getObject(Locale::class, ['lists' => $lists]);

        $this->deployFilesystem = $objectManager->getObject(
            DeployFilesystem::class,
            [
                'storeView' => $this->storeView,
                'shell' => $this->shell,
                'filesystem' => $this->filesystem,
                'userCollection' => $this->userCollection,
<<<<<<< HEAD
                'locale' => $locale
=======
                'locale' => $locale,
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            ]
        );

        $this->cmdPrefix = PHP_BINARY . ' -f ' . BP . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'magento ';
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testRegenerateStatic()
    {
        $storeLocales = ['fr_FR', 'de_DE', 'nl_NL'];
        $this->storeView->method('retrieveLocales')
            ->willReturn($storeLocales);

        $setupDiCompileCmd = $this->cmdPrefix . 'setup:di:compile';
<<<<<<< HEAD
        $this->shell->expects(self::at(0))
            ->method('execute')
            ->with($setupDiCompileCmd);

        $this->initAdminLocaleMock('en_US');

        $usedLocales = ['fr_FR', 'de_DE', 'nl_NL', 'en_US'];
        $staticContentDeployCmd = $this->cmdPrefix . 'setup:static-content:deploy -f '
            . implode(' ', $usedLocales);
        $this->shell->expects(self::at(1))
            ->method('execute')
            ->with($staticContentDeployCmd);
=======
        $this->initAdminLocaleMock('en_US');

        $usedLocales = ['fr_FR', 'de_DE', 'nl_NL', 'en_US'];
        $cacheFlushCmd = $this->cmdPrefix . 'cache:flush';
        $staticContentDeployCmd = $this->cmdPrefix . 'setup:static-content:deploy -f '
            . implode(' ', $usedLocales);
        $this->shell
            ->expects($this->exactly(4))
            ->method('execute')
            ->withConsecutive([$cacheFlushCmd], [$setupDiCompileCmd], [$cacheFlushCmd], [$staticContentDeployCmd]);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

        $this->output->expects(self::at(0))
            ->method('writeln')
            ->with('Starting compilation');
        $this->output->expects(self::at(2))
            ->method('writeln')
            ->with('Compilation complete');
        $this->output->expects(self::at(3))
            ->method('writeln')
            ->with('Starting deployment of static content');
        $this->output->expects(self::at(5))
            ->method('writeln')
            ->with('Deployment of static content complete');

        $this->deployFilesystem->regenerateStatic($this->output);
    }

    /**
     * Checks a case when configuration contains incorrect locale code.
     *
<<<<<<< HEAD
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ;echo argument has invalid value, run info:language:list for list of available locales
=======
     * @return void
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ;echo argument has invalid value, run info:language:list for list of available locales
     * @throws \Magento\Framework\Exception\LocalizedException
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function testGenerateStaticForNotAllowedStoreViewLocale()
    {
        $storeLocales = ['fr_FR', 'de_DE', ';echo'];
        $this->storeView->method('retrieveLocales')
            ->willReturn($storeLocales);

        $this->initAdminLocaleMock('en_US');

        $this->deployFilesystem->regenerateStatic($this->output);
    }

    /**
     * Checks as case when admin locale is incorrect.
     *
<<<<<<< HEAD
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ;echo argument has invalid value, run info:language:list for list of available locales
=======
     * @return void
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ;echo argument has invalid value, run info:language:list for list of available locales
     * @throws \Magento\Framework\Exception\LocalizedException
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function testGenerateStaticForNotAllowedAdminLocale()
    {
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
<<<<<<< HEAD
=======
     * @return void
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private function initAdminLocaleMock($locale)
    {
        /** @var User|MockObject $user */
<<<<<<< HEAD
        $user = $this->createMock(User::class);
=======
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->getMock();
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $user->method('getInterfaceLocale')
            ->willReturn($locale);
        $this->userCollection->method('getIterator')
            ->willReturn(new \ArrayIterator([$user]));
    }
}
