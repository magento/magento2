<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader;

class FileScannerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\FileScanner
     */
    private $object;

    protected function setUp()
    {
        $this->object = new \Magento\Setup\Module\Di\Code\Reader\FileScanner(
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
