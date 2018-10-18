<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\ObjectManager\ConfigWriter;

class FilesystemTest extends \PHPUnit\Framework\TestCase
{
    const CACHE_KEY = 'filesystemtest';

    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigWriter\Filesystem
     */
    private $configWriter;

    /**
     * @var \Magento\Framework\App\ObjectManager\ConfigLoader
     */
    private $configReader;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->configWriter = $objectManager->create(
            \Magento\Framework\App\ObjectManager\ConfigWriter\Filesystem::class
        );
        $this->configReader = $objectManager->create(
            \Magento\Framework\App\ObjectManager\ConfigLoader\Compiled::class
        );
    }

    public function testWrite()
    {
        $sampleData = [
            'classA' => true,
            'classB' => false,
        ];

        $this->configWriter->write(self::CACHE_KEY, $sampleData);
        $this->assertEquals($sampleData, $this->configReader->load(self::CACHE_KEY));
    }

    public function testOverwrite()
    {
        $this->configWriter->write(self::CACHE_KEY, ['hello' => 'world']);

        $sampleData = [
            'classC' => false,
            'classD' => true,
        ];

        $this->configWriter->write(self::CACHE_KEY, $sampleData);
        $this->assertEquals($sampleData, $this->configReader->load(self::CACHE_KEY));
    }
}
