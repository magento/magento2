<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $this->encryptorInterfaceMock = $this->getMockForAbstractClass(EncryptorInterface::class);
        $this->filesystemMock = $this->createMock(Filesystem::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);
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
        $dir = $this->getMockForAbstractClass(ReadInterface::class);
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
        $dir = $this->getMockForAbstractClass(ReadInterface::class);
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
        $dir = $this->getMockForAbstractClass(ReadInterface::class);
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
