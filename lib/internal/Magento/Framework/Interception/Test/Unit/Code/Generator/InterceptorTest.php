<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Interception\Test\Unit\Code\Generator;

use Composer\Autoload\ClassLoader;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Interception\Code\Generator\Interceptor;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

class InterceptorTest extends \PHPUnit\Framework\TestCase
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
            ->setMethods(['_validateData'])
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
    public function interceptorDataProvider()
    {
        return [
            [
                \Magento\Framework\Interception\Code\Generator\Sample::class,
                \Magento\Framework\Interception\Code\Generator\Sample\Interceptor::class,
                'Interceptor'
            ],
            [
                \Magento\Framework\Interception\Code\Generator\TSample::class,
                \Magento\Framework\Interception\Code\Generator\TSample\Interceptor::class,
                'TInterceptor'
            ]
        ];
    }
}
