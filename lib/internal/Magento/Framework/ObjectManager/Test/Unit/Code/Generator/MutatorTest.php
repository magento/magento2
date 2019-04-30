<?php
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Code\Generator;

use Composer\Autoload\ClassLoader;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\ObjectManager\Code\Generator\Mutator;
use Magento\Framework\ObjectManager\Code\Generator\SampleDto;
use PHPUnit\Framework\TestCase;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class MutatorTest extends TestCase
{
    /**
     * @var Io|MockObject
     */
    private $ioGenerator;

    /**
     * @inheritdoc
     */
    protected function setUp()
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
     * @param string $className
     * @param string $sourceClassName
     * @param string $fileName
     * @dataProvider interfaceListDataProvider
     */
    public function testGenerate($className, $sourceClassName, $fileName)
    {
        /** @var Mutator|MockObject $mutator */
        $mutator = $this->getMockBuilder(Mutator::class)
            ->setMethods(['_validateData'])
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

        $mutatorCode = file_get_contents(__DIR__ . '/_files/' . $fileName . '.txt');
        $this->ioGenerator->method('writeResultFile')
            ->with($fileName . '.php', $mutatorCode);

        $mutator->method('_validateData')
            ->willReturn(true);
        $generated = $mutator->generate();

        $this->assertEquals($fileName . '.php', $generated, 'Generated mutator is invalid.');
    }

    /**
     * @return array
     */
    public function interfaceListDataProvider(): array
    {
        return [
            [
                SampleDto::class . 'Mutator',
                SampleDto::class,
                'SampleDtoMutator'
            ]
        ];
    }
}
