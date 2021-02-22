<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model;

use Magento\Analytics\Model\FileInfo;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class FileInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    /**
     * @param string|null $path
     * @param string|null $initializationVector
     * @return void
     * @dataProvider constructDataProvider
     */
    public function testConstruct($path, $initializationVector)
    {
        $constructorArguments = [
            'path' => $path,
            'initializationVector' => $initializationVector,
        ];
        /** @var FileInfo $fileInfo */
        $fileInfo = $this->objectManagerHelper->getObject(
            FileInfo::class,
            array_filter($constructorArguments)
        );

        $this->assertSame($path ?: '', $fileInfo->getPath());
        $this->assertSame($initializationVector ?: '', $fileInfo->getInitializationVector());
    }

    /**
     * @return array
     */
    public function constructDataProvider()
    {
        return [
            'Degenerate object' => [null, null],
            'Without Initialization Vector' => ['content text', null],
            'With Initialization Vector' => ['content text', 'c51sd3c4sd68c5sd'],
        ];
    }
}
