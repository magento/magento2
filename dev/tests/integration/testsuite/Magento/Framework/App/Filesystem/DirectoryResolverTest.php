<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Filesystem;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test class for the \Magento\Framework\App\Filesystem\DirectoryResolver verification.
 */
class DirectoryResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryResolver
     */
    private $directoryResolver;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->directoryResolver = $this->objectManager
            ->create(\Magento\Framework\App\Filesystem\DirectoryResolver::class);
        $this->filesystem = $this->objectManager->create(\Magento\Framework\Filesystem::class);
    }

    /**
     * @param string $path
     * @param string $directoryConfig
     * @param bool $expectation
     * @dataProvider validatePathDataProvider
     * @magentoAppIsolation enabled
     * @return void
     */
    public function testValidatePath($path, $directoryConfig, $expectation)
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $path = $directory->getAbsolutePath() .'/' .$path;
        $this->assertEquals($expectation, $this->directoryResolver->validatePath($path, $directoryConfig));
    }

    /**
     * @expectedException \Magento\Framework\Exception\FileSystemException
     * @magentoAppIsolation enabled
     */
    public function testValidatePathWithException()
    {
        $directory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $path = $directory->getAbsolutePath();
        $this->directoryResolver->validatePath($path, 'wrong_dir');
    }

    /**
     * @return array
     */
    public function validatePathDataProvider()
    {
        return [
            [
                '/',
                DirectoryList::MEDIA,
                true,
            ],
            [
                '/../../pub/',
                DirectoryList::MEDIA,
                false,
            ],
        ];
    }
}
