<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader;

use Magento\Setup\Module\Di\Code\Reader\FileScanner;
use PHPUnit\Framework\TestCase;

class FileScannerTest extends TestCase
{
    /**
     * @var FileScanner
     */
    private $object;

    protected function setUp(): void
    {
        $this->object = new FileScanner(
            __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'classes.php'
        );
    }

    public function testGetClassesReturnsAllClassesAndInterfacesDeclaredInFile()
    {
        $classes = [
            'My\NamespaceA\InterfaceA',
            'My\NamespaceA\ClassA',
            'My\NamespaceB\InterfaceB',
            'My\NamespaceB\ClassB',
        ];
        $this->assertCount(4, $this->object->getClasses());
        foreach ($this->object->getClasses() as $key => $class) {
            $this->assertEquals($classes[$key], $class->getName());
        }
    }
}
