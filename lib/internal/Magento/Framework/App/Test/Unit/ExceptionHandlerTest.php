<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\ExceptionHandler;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\SetupInfo;
use Magento\Framework\Debug;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Exception\State\InitException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Phrase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Constraint\StringStartsWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for \Magento\Framework\App\ExceptionHandler class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExceptionHandlerTest extends TestCase
{
    /**
     * @var ExceptionHandler
     */
    private $exceptionHandler;

    /**
     * @var EncryptorInterface|MockObject
     */
    private $encryptorInterfaceMock;

    /**
     * @var FileSystem|MockObject
     */
    private $filesystemMock;

    /**
     * @var LoggerInterface|MockObject
     */
    private $loggerMock;

    /**
     * @var ResponseHttp|MockObject
     */
    private $responseMock;

    /**
     * @var RequestHttp|MockObject
     */
    private $requestMock;

    protected function setUp(): void
    {
        $this->encryptorInterfaceMock = $this->createMock(EncryptorInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->responseMock = $this->createMock(ResponseHttp::class);
        $this->requestMock = $this->getMockBuilder(RequestHttp::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->exceptionHandler = new ExceptionHandler(
            $this->encryptorInterfaceMock,
            $this->filesystemMock,
            $this->loggerMock
        );
    }

    public function testHandleDeveloperModeNotInstalled()
    {
        $dir = $this->createMock(ReadInterface::class);
        $dir->expects($this->once())
            ->method('getAbsolutePath')
            ->willReturn(__DIR__);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::ROOT)
            ->willReturn($dir);
        $this->responseMock->expects($this->once())
            ->method('setRedirect')
            ->with('/_files/');
        $this->responseMock->expects($this->once())
            ->method('sendHeaders');
        $bootstrap = $this->getBootstrapNotInstalled();
        $bootstrap->expects($this->once())
            ->method('getParams')
            ->willReturn(
                [
                    'SCRIPT_NAME' => '/index.php',
                    'DOCUMENT_ROOT' => __DIR__,
                    'SCRIPT_FILENAME' => __DIR__ . '/index.php',
                    SetupInfo::PARAM_NOT_INSTALLED_URL_PATH => '_files',
                ]
            );
        $this->assertTrue(
            $this->exceptionHandler->handle(
                $bootstrap,
                new \Exception('Test Message'),
                $this->responseMock,
                $this->requestMock
            )
        );
    }

    public function testHandleDeveloperMode()
    {
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->willThrowException(new \Exception('strange error'));
        $this->responseMock->expects($this->once())
            ->method('setHttpResponseCode')
            ->with(500);
        $this->responseMock->expects($this->once())
            ->method('setHeader')
            ->with('Content-Type', 'text/plain');
        $constraint = new StringStartsWith('1 exception(s):');
        $this->responseMock->expects($this->once())
            ->method('setBody')
            ->with($constraint);
        $this->responseMock->expects($this->once())
            ->method('sendResponse');
        $bootstrap = $this->getBootstrapNotInstalled();
        $bootstrap->expects($this->once())
            ->method('getParams')
            ->willReturn(
                ['DOCUMENT_ROOT' => 'something', 'SCRIPT_FILENAME' => 'something/else']
            );
        $this->assertTrue(
            $this->exceptionHandler->handle(
                $bootstrap,
                new \Exception('Test'),
                $this->responseMock,
                $this->requestMock
            )
        );
    }

    /**
     * @param string $message
     * @return void
     */
    #[DataProvider('exceptionMessageDataProvider')]
    public function testHandleDeveloperModeIncludesThrowLocation(string $message): void
    {
        $throwLine = __LINE__ + 1;
        $exception = new \Exception($message);
        $this->assertDeveloperModeResponse($exception, [
            sprintf(
                'Exception #0 (Exception): "%s" thrown at [%s:%d]',
                $message,
                'lib/internal/Magento/Framework/App/Test/Unit/ExceptionHandlerTest.php',
                $throwLine
            )
        ]);
    }

    /**
     * @return array
     */
    public static function exceptionMessageDataProvider(): array
    {
        return [
            'plain message' => ['Test Message'],
            'empty message' => [''],
            'quoted message' => ['Failed for "customer\'s order" with 100% <data>']
        ];
    }

    /**
     * @return void
     */
    public function testHandleDeveloperModeIncludesChainedErrorLocations(): void
    {
        $errorLine = __LINE__ + 1;
        $error = new \Error('Engine "failure"');
        $exceptionLine = __LINE__ + 1;
        $exception = new \RuntimeException('Outer failure', 0, $error);
        $file = 'lib/internal/Magento/Framework/App/Test/Unit/ExceptionHandlerTest.php';
        $this->assertDeveloperModeResponse($exception, [
            sprintf('Exception #0 (RuntimeException): "Outer failure" thrown at [%s:%d]', $file, $exceptionLine),
            sprintf('Exception #1 (Error): "Engine "failure"" thrown at [%s:%d]', $file, $errorLine)
        ]);
    }

    /**
     * Assert the developer response contains each header in both the summary and trace sections.
     *
     * @param \Exception $exception
     * @param string[] $headers
     * @return void
     */
    private function assertDeveloperModeResponse(\Exception $exception, array $headers): void
    {
        $this->encryptorInterfaceMock->expects($this->never())->method('getHash');
        $this->filesystemMock->expects($this->never())->method('getDirectoryRead');
        $this->loggerMock->expects($this->never())->method('critical');
        $this->requestMock->expects($this->never())->method('getDistroBaseUrl');
        $bootstrap = $this->createMock(Bootstrap::class);
        $bootstrap->expects($this->once())->method('isDeveloperMode')->willReturn(true);
        $bootstrap->expects($this->once())->method('getErrorCode')->willReturn(0);
        $this->responseMock->expects($this->once())->method('clearHeader')->with('Location');
        $this->responseMock->expects($this->once())->method('setHttpResponseCode')->with(500);
        $this->responseMock->expects($this->once())->method('setHeader')->with('Content-Type', 'text/plain');
        $this->responseMock->expects($this->once())->method('sendResponse');
        $this->responseMock->expects($this->once())->method('setBody')->with($this->callback(
            function (string $body) use ($headers): bool {
                $this->assertStringStartsWith(
                    count($headers) . " exception(s):\n" . implode("\n", $headers) . "\n\n",
                    $body
                );
                foreach ($headers as $header) {
                    $this->assertSame(2, substr_count($body, $header . "\n"));
                    $this->assertStringContainsString("\n" . $header . "\n<pre>", $body);
                }
                return true;
            }
        ));

        $this->assertTrue($this->exceptionHandler->handle(
            $bootstrap,
            $exception,
            $this->responseMock,
            $this->requestMock
        ));
    }

    public function testCatchExceptionSessionException()
    {
        $this->responseMock->expects($this->once())
            ->method('setRedirect');
        $this->responseMock->expects($this->once())
            ->method('sendHeaders');
        /** @var Bootstrap|MockObject $bootstrap */
        $bootstrap = $this->createMock(Bootstrap::class);
        $bootstrap->expects($this->once())
            ->method('isDeveloperMode')
            ->willReturn(false);
        $this->assertTrue(
            $this->exceptionHandler->handle(
                $bootstrap,
                new SessionException(new Phrase('Test')),
                $this->responseMock,
                $this->requestMock
            )
        );
    }

    public function testHandleInitException()
    {
        $bootstrap = $this->getBootstrapInstalled();
        $exception = new InitException(new Phrase('Test'));
        $dir = $this->createMock(ReadInterface::class);
        $dir->expects($this->once())
            ->method('getAbsolutePath')
            ->with('errors/404.php')
            ->willReturn(__DIR__ . '/_files/pub/errors/404.php');
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::PUB)
            ->willReturn($dir);

        $this->assertTrue(
            $this->exceptionHandler->handle(
                $bootstrap,
                $exception,
                $this->responseMock,
                $this->requestMock
            )
        );
    }

    public function testHandleGenericReport()
    {
        $bootstrap = $this->getBootstrapInstalled();
        $exception = new \Exception('Test');
        $dir = $this->createMock(ReadInterface::class);
        $dir->expects($this->once())
            ->method('getAbsolutePath')
            ->with('errors/report.php')
            ->willReturn(__DIR__ . '/_files/pub/errors/report.php');
        $bootstrap->expects($this->once())
            ->method('getParams')
            ->willReturn(['REQUEST_URI' => 'some-request-uri', 'SCRIPT_NAME' => 'some-script-name']);
        $reportData = [
            $exception->getMessage(),
            Debug::trace(
                $exception->getTrace(),
                true,
                false,
                (bool)getenv('MAGE_DEBUG_SHOW_ARGS')
            ),
            'url' => 'some-request-uri',
            'script_name' => 'some-script-name'
        ];
        $this->encryptorInterfaceMock->expects($this->once())
            ->method('getHash')
            ->with(implode('', $reportData))
            ->willReturn('some-sha256-hash');
        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($exception, ['report_id' => 'some-sha256-hash']);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::PUB)
            ->willReturn($dir);

        $this->assertTrue(
            $this->exceptionHandler->handle(
                $bootstrap,
                $exception,
                $this->responseMock,
                $this->requestMock
            )
        );
    }

    /**
     * Prepares a mock of bootstrap in "not installed" state
     *
     * @return Bootstrap|MockObject
     */
    private function getBootstrapNotInstalled(): Bootstrap
    {
        $bootstrap = $this->createMock(Bootstrap::class);
        $bootstrap->expects($this->once())
            ->method('isDeveloperMode')
            ->willReturn(true);
        $bootstrap->expects($this->once())
            ->method('getErrorCode')
            ->willReturn(Bootstrap::ERR_IS_INSTALLED);
        return $bootstrap;
    }

    /**
     * Prepares a mock of bootstrap in "installed" state
     *
     * @return Bootstrap|MockObject
     */
    private function getBootstrapInstalled(): Bootstrap
    {
        /** @var Bootstrap|MockObject $bootstrap */
        $bootstrap = $this->createMock(Bootstrap::class);
        $bootstrap->expects($this->once())
            ->method('isDeveloperMode')
            ->willReturn(false);
        $bootstrap->expects($this->once())
            ->method('getErrorCode')
            ->willReturn(0);
        return $bootstrap;
    }
}
