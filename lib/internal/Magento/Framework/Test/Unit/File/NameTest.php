<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Unit\File;

use Magento\Framework\File\Name;

class NameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $existingFilePath;

    /**
     * @var string
     */
    private $nonExistingFilePath;

    /**
     * @var Name
     */
    private $name;

    protected function setUp()
    {
        $this->name = new Name();
        $this->existingFilePath = __DIR__ . '/../_files/source.txt';
        $this->nonExistingFilePath = __DIR__ . '/../_files/file.txt';
    }

    /**
     * @test
     */
    public function testGetNewFileNameWhenFileExists()
    {
        $this->assertEquals('source_1.txt', $this->name->getNewFileName($this->existingFilePath));
    }

    /**
     * @test
     */
    public function testGetNewFileNameWhenFileDoesNotExist()
    {
        $this->assertEquals('file.txt', $this->name->getNewFileName($this->nonExistingFilePath));
    }
}
