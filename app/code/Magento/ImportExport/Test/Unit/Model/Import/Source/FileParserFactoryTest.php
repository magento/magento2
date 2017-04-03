<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source;

use Magento\Framework\ObjectManagerInterface;
use Magento\ImportExport\Model\Import\Source\FileParserFactory;
use Magento\ImportExport\Model\Import\Source\FileParserInterface;

class FileParserFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Missing extension in parser definition
     * @test
     */
    public function when_extension_is_not_provided_it_throws_InvalidArgumentException()
    {
        $this->createFileParserFactory([[]]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Missing class in parser definition
     * @test
     */
    public function when_class_is_empty_it_throws_InvalidArgumentException()
    {
        $this->createFileParserFactory([
            ['extension' => 'csv']
        ]);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Missing argument in parser definition
     * @test
     */
    public function when_argument_is_empty_it_throws_InvalidArgumentException()
    {
        $this->createFileParserFactory([
            [
                'extension' => 'csv',
                'class' => 'SomeClass'
            ]
        ]);
    }


    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage File "some/random/file-path.unknowntype" is an invalid format
     * @test
     */
    public function when_non_existing_file_type_is_provided_it_throws_InvalidArgumentException()
    {
        $factory = $this->createFileParserFactory(
            [
                [
                    'extension' => 'myfile',
                    'class' => FileParserInterface::class,
                    'argument' => 'file'
                ]
            ]
        );

        $factory->create('some/random/file-path.unknowntype');
    }

    /**
     * @test
     */
    public function when_mapped_type_is_provided_it_creates_instance_via_object_manager()
    {
        $fileParser = $this->getMockForAbstractClass(
            FileParserInterface::class,
            [],
            'FileParserStub'
        );

        $factory = $this->createFileParserFactory(
            [
                $this->createParserMapArray('myfile', 'FileParserStub', 'file')
            ],
            $this->createMockedObjectManagerForFileParser(
                'FileParserStub',
                ['file' => 'some/random/file-path.myfile'],
                $fileParser
            )
        );

        $this->assertSame($fileParser, $factory->create('some/random/file-path.myfile'));
    }

    /**
     * @test
     */
    public function when_additional_options_are_provided_it_passes_them_to_constructor_of_file_parser()
    {
        $fileParser = $this->getMockForAbstractClass(FileParserInterface::class);
        $fileParserClass = get_class($fileParser);


        $factory = $this->createFileParserFactory(
            [
                $this->createParserMapArray('myfile', $fileParserClass, 'file')
            ],
            $this->createMockedObjectManagerForFileParser(
                $fileParserClass,
                [
                    'file' => 'some/random/file-path.myfile',
                    'option1' => 'option1',
                    'option2' => 'option2'
                ],
                $fileParser
            )
        );

        $this->assertSame(
            $fileParser,
            $factory->create(
                'some/random/file-path.myfile',
                [
                    'option1' => 'option1',
                    'option2' => 'option2'
                ]
            )
        );
    }

    /**
     * @return FileParserFactory
     */
    private function createFileParserFactory($typeMap = [], ObjectManagerInterface $objectManager = null)
    {
        $factory = new FileParserFactory(
            $objectManager ?: $this->getMockForAbstractClass(ObjectManagerInterface::class),
            $typeMap
        );
        return $factory;
    }

    /**
     * @return ObjectManagerInterface
     */
    private function createMockedObjectManagerForFileParser(
        $className,
        $arguments,
        FileParserInterface $fileParser
    ) {
        $objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $objectManager->expects($this->once())
            ->method('create')
            ->with($className, $arguments)
            ->willReturn($fileParser);

        return $objectManager;
    }

    private function createParserMapArray($extension, $className, $argumentName)
    {
        return [
            'extension' => $extension,
            'class' => $className,
            'argument' => $argumentName
        ];
    }
}
