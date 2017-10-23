<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Test\Unit\Model;

class FilesystemTest extends \PHPUnit\Framework\TestCase
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

        $this->storeViewMock = $this->createMock(\Magento\Store\Model\Config\StoreView::class);
        $this->shellMock = $this->createMock(\Magento\Framework\ShellInterface::class);
        $this->userCollectionMock = $this->createMock(\Magento\User\Model\ResourceModel\User\Collection::class);
        $this->outputMock = $this->createMock(\Symfony\Component\Console\Output\OutputInterface::class);
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->filesystemMock = $this->createMock(\Magento\Framework\Filesystem::class);
        $this->directoryWriteMock = $this->createMock(\Magento\Framework\Filesystem\Directory\WriteInterface::class);
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
        $userMock = $this->createMock(\Magento\User\Model\User::class);
        $userMock->expects($this->once())
            ->method('getInterfaceLocale')
            ->willReturn('en_US');
        $this->userCollectionMock->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([$userMock]));

        $usedLocales = array_unique(
            array_merge($storeLocales, $adminUserInterfaceLocales)
        );
        $staticContentDeployCmd = $this->cmdPrefix . 'setup:static-content:deploy -f '
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
