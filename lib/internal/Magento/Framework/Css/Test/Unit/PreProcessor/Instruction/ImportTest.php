<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Css\Test\Unit\PreProcessor\Instruction;

use Magento\Framework\Css\PreProcessor\FileGenerator\RelatedGenerator;
use Magento\Framework\Css\PreProcessor\Instruction\Import;

/**
 * Class ImportTest
 */
class ImportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\NotationResolver\Module|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notationResolver;

    /**
     * @var \Magento\Framework\View\Asset\File|\PHPUnit_Framework_MockObject_MockObject
     */
    private $asset;

    /**
     * @var Import
     */
    private $object;

    /**
     * @var RelatedGenerator
     */
    private $relatedFileGeneratorMock;

    protected function setUp()
    {

        $this->notationResolver = $this->getMock(
            '\Magento\Framework\View\Asset\NotationResolver\Module', [], [], '', false
        );
        $this->asset = $this->getMock('\Magento\Framework\View\Asset\File', [], [], '', false);
        $this->asset->expects($this->any())->method('getContentType')->will($this->returnValue('css'));

        $this->relatedFileGeneratorMock = $this->getMockBuilder(RelatedGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = new Import($this->notationResolver, $this->relatedFileGeneratorMock);
    }

    /**
     * @param string $originalContent
     * @param string $foundPath
     * @param string $resolvedPath
     * @param string $expectedContent
     *
     * @dataProvider processDataProvider
     */
    public function testProcess($originalContent, $foundPath, $resolvedPath, $expectedContent)
    {
        $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain($this->asset, $originalContent, 'less', 'path');
        $this->notationResolver->expects($this->once())
            ->method('convertModuleNotationToPath')
            ->with($this->asset, $foundPath)
            ->will($this->returnValue($resolvedPath));
        $this->object->process($chain);
        $this->assertEquals($expectedContent, $chain->getContent());
        $this->assertEquals('less', $chain->getContentType());
    }

    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            'non-modular notation' => [
                '@import (type) "some/file.css" media;',
                'some/file.css',
                'some/file.css',
                "@import (type) 'some/file.css' media;",
            ],
            'modular, with extension' => [
                '@import (type) "Magento_Module::something.css" media;',
                'Magento_Module::something.css',
                'Magento_Module/something.css',
                "@import (type) 'Magento_Module/something.css' media;",
            ],
            'modular, no extension' => [
                '@import (type) "Magento_Module::something" media;',
                'Magento_Module::something.less',
                'Magento_Module/something.less',
                "@import (type) 'Magento_Module/something.less' media;",
            ],
            'no type' => [
                '@import "Magento_Module::something.css" media;',
                'Magento_Module::something.css',
                'Magento_Module/something.css',
                "@import 'Magento_Module/something.css' media;",
            ],
            'no media' => [
                '@import (type) "Magento_Module::something.css";',
                'Magento_Module::something.css',
                'Magento_Module/something.css',
                "@import (type) 'Magento_Module/something.css';",
            ],
            'with single line comment' => [
                '@import (type) "some/file.css" media;' . PHP_EOL
                    . '// @import (type) "unnecessary/file.css" media;',
                'some/file.css',
                'some/file.css',
                "@import (type) 'some/file.css' media;" . PHP_EOL,
            ],
            'with multi line comment' => [
                '@import (type) "some/file.css" media;' . PHP_EOL
                    . '/* @import (type) "unnecessary/file.css" media;' . PHP_EOL
                    . '@import (type) "another/unnecessary/file.css" media; */',
                'some/file.css',
                'some/file.css',
                "@import (type) 'some/file.css' media;" . PHP_EOL,
            ],
        ];
    }

    public function testProcessNoImport()
    {
        $originalContent = 'color: #000000;';
        $expectedContent = 'color: #000000;';

        $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain($this->asset, $originalContent, 'css', 'path');
        $this->notationResolver->expects($this->never())
            ->method('convertModuleNotationToPath');
        $this->object->process($chain);
        $this->assertEquals($expectedContent, $chain->getContent());
        $this->assertEquals('css', $chain->getContentType());
    }

    /**
     * @covers \Magento\Framework\Css\PreProcessor\Instruction\Import::resetRelatedFiles
     */
    public function testGetRelatedFiles()
    {
        $this->assertSame([], $this->object->getRelatedFiles());

        $this->notationResolver->expects($this->once())
            ->method('convertModuleNotationToPath')
            ->with($this->asset, 'Magento_Module::something.css')
            ->will($this->returnValue('Magento_Module/something.css'));
        $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain(
            $this->asset,
            '@import (type) "Magento_Module::something.css" media;',
            'css',
            'path'
        );
        $this->object->process($chain);
        $chain = new \Magento\Framework\View\Asset\PreProcessor\Chain($this->asset, 'color: #000000;', 'css', 'path');
        $this->object->process($chain);

        $expected = [['Magento_Module::something.css', $this->asset]];
        $this->assertSame($expected, $this->object->getRelatedFiles());

        $this->object->resetRelatedFiles();
        $this->assertSame([], $this->object->getRelatedFiles());
    }
}
