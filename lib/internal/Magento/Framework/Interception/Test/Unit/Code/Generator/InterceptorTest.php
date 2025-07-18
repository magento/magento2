<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Test\Unit\Code\Generator;

use Composer\Autoload\ClassLoader;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Interception\Code\Generator\Interceptor;
use Magento\Framework\Interception\Code\Generator\ReflectionIntersectionTypeSample;
use Magento\Framework\Interception\Code\Generator\ReflectionUnionTypeSample;
use Magento\Framework\Interception\Code\Generator\Sample;
use Magento\Framework\Interception\Code\Generator\SampleBackendMenu;
use Magento\Framework\Interception\Code\Generator\TSample;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InterceptorTest extends TestCase
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
            'Magento\\Framework\\Interception\\Code\\Generator\\',
            __DIR__ . '/_files'
        );
        $loader->register();
    }

    /**
     * Checks a test case when interceptor generates code for the specified class.
     *
     * @param string $className
     * @param string $resultClassName
     * @param string $fileName
     * @dataProvider interceptorDataProvider
     */
    public function testGenerate($className, $resultClassName, $fileName)
    {
        /** @var Interceptor|MockObject $interceptor */
        $interceptor = $this->getMockBuilder(Interceptor::class)
            ->onlyMethods(['_validateData'])
            ->setConstructorArgs([
                $className,
                $resultClassName,
                $this->ioGenerator,
            ])
            ->getMock();

        $this->ioGenerator
            ->method('generateResultFileName')
            ->with('\\' . $resultClassName)
            ->willReturn($fileName . '.php');

        $code = file_get_contents(__DIR__ . '/_files/' . $fileName . '.txt');
        $this->ioGenerator->method('writeResultFile')
            ->with($fileName . '.php', $code);

        $interceptor->method('_validateData')
            ->willReturn(true);

        $generated = $interceptor->generate();
        $this->assertEquals($fileName . '.php', $generated, 'Generated interceptor is invalid.');
    }

    /**
     * Gets list of interceptor samples.
     *
     * @return array
     */
    public static function interceptorDataProvider()
    {
        return [
            [
                Sample::class,
                Sample\Interceptor::class,
                'Interceptor'
            ],
            [
                TSample::class,
                TSample\Interceptor::class,
                'TInterceptor'
            ],
            [
                SampleBackendMenu::class,
                SampleBackendMenu\Interceptor::class,
                'SampleBackendMenuInterceptor',
            ],
            [
                ReflectionUnionTypeSample::class,
                ReflectionUnionTypeSample\Interceptor::class,
                'ReflectionUnionTypeSampleInterceptor',
            ],
            [
                ReflectionIntersectionTypeSample::class,
                ReflectionIntersectionTypeSample\Interceptor::class,
                'ReflectionIntersectionTypeSampleInterceptor',
            ],
        ];
    }
}
