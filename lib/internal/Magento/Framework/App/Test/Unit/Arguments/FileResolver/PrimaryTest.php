<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Arguments\FileResolver;

use Magento\Framework\App\Arguments\FileResolver\Primary;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\FileIteratorFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PrimaryTest extends TestCase
{
    /**     * @param string $filename
     */
    #[DataProvider('getMethodDataProvider')]
    public function testGet(array $fileList, $scope, $filename)
    {
        $directory = $this->createMock(Read::class);
        $filesystem = $this->createMock(Filesystem::class);
        $iteratorFactory = $this->createPartialMock(FileIteratorFactory::class, ['create']);

        $filesystem->expects(
            $this->once()
        )->method(
            'getDirectoryRead'
        )->with(
            DirectoryList::CONFIG
        )->willReturn(
            $directory
        );

        $directory->expects($this->once())->method('search')->willReturn($fileList);

        $iteratorFactory->expects($this->once())->method('create')->willReturn(true);

        $model = new Primary($filesystem, $iteratorFactory);

        $this->assertTrue($model->get($filename, $scope));
    }

    /**
     * @return array
     */
    public static function getMethodDataProvider()
    {
        return [[['config/di.xml', 'config/some_config/di.xml'], 'primary', 'di.xml']];
    }
}
