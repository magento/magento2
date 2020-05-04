<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\I18n;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Setup\Module\I18n\FilesCollector;
use PHPUnit\Framework\TestCase;

class FilesCollectorTest extends TestCase
{
    /**
     * @var string
     */
    protected $_testDir;

    /**
     * @var FilesCollector
     */
    protected $_filesCollector;

    protected function setUp(): void
    {
        $this->_testDir = str_replace('\\', '/', realpath(dirname(__FILE__))) . '/_files/files_collector/';

        $objectManagerHelper = new ObjectManager($this);
        $this->_filesCollector = $objectManagerHelper->getObject(FilesCollector::class);
    }

    public function testGetFilesWithoutMask()
    {
        $expectedResult = [$this->_testDir . 'default.xml', $this->_testDir . 'file.js'];
        $files = $this->_filesCollector->getFiles([$this->_testDir]);
        $this->assertEquals($expectedResult, $files);
    }

    public function testGetFilesWithMask()
    {
        $expectedResult = [$this->_testDir . 'file.js'];
        $this->assertEquals($expectedResult, $this->_filesCollector->getFiles([$this->_testDir], '/\.js$/'));
    }
}
