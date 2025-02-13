<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Code\Generator;

use Composer\Autoload\ClassLoader;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\ObjectManager\Code\Generator\Repository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenerateRepositoryTest extends TestCase
{
    /**
     * @var Io|MockObject
     */
    private $ioGenerator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->ioGenerator = $this->getMockBuilder(Io::class)
            ->disableOriginalConstructor()
            ->getMock();

        $loader = new ClassLoader();
        $loader->addPsr4(
            'Magento\\Framework\\ObjectManager\\Code\\Generator\\',
            __DIR__ . '/_files'
        );
        $loader->register();
    }

    /**
     * Checks a case when repository generator uses interface.
     *
     * @param string $className
     * @param string $sourceClassName
     * @param string $fileName
     * @dataProvider interfaceListDataProvider
     */
    public function testGenerate($className, $sourceClassName, $fileName)
    {
        /** @var Repository|MockObject $repository */
        $repository = $this->getMockBuilder(Repository::class)
            ->onlyMethods(['_validateData'])
            ->setConstructorArgs([
                $sourceClassName,
                null,
                $this->ioGenerator
            ])
            ->getMock();

        $this->ioGenerator
            ->method('generateResultFileName')
            ->with('\\' . $className)
            ->willReturn($fileName . '.php');

        $repositoryCode = file_get_contents(__DIR__ . '/_files/' . $fileName . '.txt');
        $this->ioGenerator->method('writeResultFile')
            ->with($fileName . '.php', $repositoryCode);

        $repository->method('_validateData')
            ->willReturn(true);
        $generated = $repository->generate();

        $this->assertEquals($fileName . '.php', $generated, 'Generated repository is invalid.');
    }

    /**
     * Get list of different repository interfaces.
     * Some of them use PHP 7.0 syntax features.
     *
     * @return array
     */
    public static function interfaceListDataProvider()
    {
        return [
            [
                \Magento\Framework\ObjectManager\Code\Generator\SampleRepository::class,
                \Magento\Framework\ObjectManager\Code\Generator\Sample::class,
                'SampleRepository'
            ],
            [
                \Magento\Framework\ObjectManager\Code\Generator\TSampleRepository::class,
                \Magento\Framework\ObjectManager\Code\Generator\TSample::class,
                'TSampleRepository'
            ],
        ];
    }

    /**
     * test protected _validateData()
     */
    public function testValidateData()
    {
        $sourceClassName = 'Magento_Module_Controller_Index';
        $resultClassName = 'Magento_Module_Controller';

        $repository = new Repository();
        $repository->init($sourceClassName, $resultClassName);
        $this->assertFalse($repository->generate());
    }
}
