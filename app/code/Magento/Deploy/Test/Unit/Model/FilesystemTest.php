<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Store\Model\Config\StoreView
     */
    private $storeViewMock;

    /**
     * @var \Magento\Framework\ShellInterface
     */
    private $shellMock;

    /**
     * @var \Magento\User\Model\ResourceModel\User\Collection
     */
    private $userCollectionMock;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $outputMock;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystemMock;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $directoryWriteMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManagerMock;

    /**
     * @var \Magento\Deploy\Model\Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $cmdPrefix;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->storeViewMock = $this->getMock(
            \Magento\Store\Model\Config\StoreView::class,
            [],
            [],
            '',
            false
        );
        $this->shellMock = $this->getMock(
            \Magento\Framework\ShellInterface::class,
            [],
            [],
            '',
            false
        );
        $this->userCollectionMock = $this->getMock(
            \Magento\User\Model\ResourceModel\User\Collection::class,
            [],
            [],
            '',
            false
        );
        $this->outputMock = $this->getMock(
            \Symfony\Component\Console\Output\OutputInterface::class,
            [],
            [],
            '',
            false
        );
        $this->objectManagerMock = $this->getMock(
            \Magento\Framework\ObjectManagerInterface::class,
            [],
            [],
            '',
            false
        );
        $this->filesystemMock = $this->getMock(
            \Magento\Framework\Filesystem::class,
            [],
            [],
            '',
            false
        );
        $this->directoryWriteMock = $this->getMock(
            \Magento\Framework\Filesystem\Directory\WriteInterface::class,
            [],
            [],
            '',
            false
        );
        $this->filesystemMock->expects($this->any())
            ->method('getDirectoryWrite')
            ->willReturn($this->directoryWriteMock);
        $this->filesystem = $objectManager->getObject(
            \Magento\Deploy\Model\Filesystem::class,
            [
                'storeView' => $this->storeViewMock,
                'shell' => $this->shellMock,
                'filesystem' => $this->filesystemMock
            ]
        );

        $userCollection = new \ReflectionProperty(\Magento\Deploy\Model\Filesystem::class, 'userCollection');
        $userCollection->setAccessible(true);
        $userCollection->setValue($this->filesystem, $this->userCollectionMock);

        $this->cmdPrefix = PHP_BINARY . ' -f ' . BP . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'magento ';
    }

    public function testRegenerateStatic()
    {
        $storeLocales = ['fr_FR', 'de_DE', 'nl_NL'];
        $adminUserInterfaceLocales = ['de_DE', 'en_US'];
        $this->storeViewMock->expects($this->once())
            ->method('retrieveLocales')
            ->willReturn($storeLocales);
        $userMock = $this->getMock(
            \Magento\User\Model\User::class,
            [],
            [],
            '',
            false
        );
        $userMock->expects($this->once())
            ->method('getInterfaceLocale')
            ->willReturn('en_US');
        $this->userCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$userMock]));

        $usedLocales = array_unique(
            array_merge($storeLocales, $adminUserInterfaceLocales)
        );
        $staticContentDeployCmd = $this->cmdPrefix . 'setup:static-content:deploy '
            . implode(' ', $usedLocales);
        $setupDiCompileCmd = $this->cmdPrefix . 'setup:di:compile';
        $this->shellMock->expects($this->at(0))
            ->method('execute')
            ->with($setupDiCompileCmd);
        $this->shellMock->expects($this->at(1))
            ->method('execute')
            ->with($staticContentDeployCmd);

        $this->outputMock->expects($this->at(0))
            ->method('writeln')
            ->with('Starting compilation');
        $this->outputMock->expects($this->at(2))
            ->method('writeln')
            ->with('Compilation complete');
        $this->outputMock->expects($this->at(3))
            ->method('writeln')
            ->with('Starting deployment of static content');
        $this->outputMock->expects($this->at(5))
            ->method('writeln')
            ->with('Deployment of static content complete');

        $this->filesystem->regenerateStatic($this->outputMock);
    }
}
