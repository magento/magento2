<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Debug;
use Magento\Framework\Filesystem;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Exception\State\InitException;

/**
 * Handler of HTTP web application exception
 */
class ExceptionHandler implements ExceptionHandlerInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param EncryptorInterface $encryptor
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     */
    public function __construct(
        EncryptorInterface $encryptor,
        Filesystem $filesystem,
        LoggerInterface $logger
    ) {
        $this->encryptor = $encryptor;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    /**
     * Handles exception of HTTP web application
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @param ResponseHttp $response
     * @param RequestHttp $request
     * @return bool
     */
    public function handle(
        Bootstrap $bootstrap,
        \Exception $exception,
        ResponseHttp $response,
        RequestHttp $request
    ): bool {
        $result = $this->handleDeveloperMode($bootstrap, $exception, $response)
            || $this->handleBootstrapErrors($bootstrap, $exception, $response)
            || $this->handleSessionException($exception, $response, $request)
            || $this->handleInitException($exception)
            || $this->handleGenericReport($bootstrap, $exception);
        return $result;
    }

    /**
     * Error handler for developer mode
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @param ResponseHttp $response
     * @return bool
     */
    private function handleDeveloperMode(
        Bootstrap $bootstrap,
        \Exception $exception,
        ResponseHttp $response
    ): bool {
        if ($bootstrap->isDeveloperMode()) {
            if (Bootstrap::ERR_IS_INSTALLED == $bootstrap->getErrorCode()) {
                try {
                    $this->redirectToSetup($bootstrap, $exception, $response);
                    return true;
                } catch (\Exception $e) {
                    $exception = $e;
                }
            }
            $response->setHttpResponseCode(500);
            $response->setHeader('Content-Type', 'text/plain');
            $response->setBody($this->buildContentFromException($exception));
            $response->sendResponse();
            return true;
        }
        return false;
    }

    /**
     * Build content based on an exception
     *
     * @param \Exception $exception
     * @return string
     */
    private function buildContentFromException(\Exception $exception): string
    {
        /** @var \Exception[] $exceptions */
        $exceptions = [];

        do {
            $exceptions[] = $exception;
        } while ($exception = $exception->getPrevious());

        $buffer = sprintf("%d exception(s):\n", count($exceptions));

        foreach ($exceptions as $index => $exception) {
            $buffer .= sprintf(
                "Exception #%d (%s): %s\n",
                $index,
                get_class($exception),
                $exception->getMessage()
            );
        }

        foreach ($exceptions as $index => $exception) {
            $buffer .= sprintf(
                "\nException #%d (%s): %s\n%s\n",
                $index,
                get_class($exception),
                $exception->getMessage(),
                Debug::trace(
                    $exception->getTrace(),
                    true,
                    true,
                    (bool)getenv('MAGE_DEBUG_SHOW_ARGS')
                )
            );
        }

        return $buffer;
    }

    /**
     * Handler for bootstrap errors
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @param ResponseHttp $response
     * @return bool
     */
    private function handleBootstrapErrors(
        Bootstrap $bootstrap,
        \Exception &$exception,
        ResponseHttp $response
    ): bool {
        $bootstrapCode = $bootstrap->getErrorCode();
        if (Bootstrap::ERR_MAINTENANCE == $bootstrapCode) {
            // phpcs:ignore Magento2.Security.IncludeFile
            require $this->filesystem
                ->getDirectoryRead(DirectoryList::PUB)
                ->getAbsolutePath('errors/503.php');
            return true;
        }
        if (Bootstrap::ERR_IS_INSTALLED == $bootstrapCode) {
            try {
                $this->redirectToSetup($bootstrap, $exception, $response);
                return true;
            } catch (\Exception $e) {
                $exception = $e;
            }
        }
        return false;
    }

    /**
     * Handler for session errors
     *
     * @param \Exception $exception
     * @param ResponseHttp $response
     * @param RequestHttp $request
     * @return bool
     */
    private function handleSessionException(
        \Exception $exception,
        ResponseHttp $response,
        RequestHttp $request
    ): bool {
        if ($exception instanceof SessionException) {
            $response->setRedirect($request->getDistroBaseUrl());
            $response->sendHeaders();
            return true;
        }
        return false;
    }

    /**
     * Handler for application initialization errors
     *
     * @param \Exception $exception
     * @return bool
     */
    private function handleInitException(\Exception $exception): bool
    {
        if ($exception instanceof InitException) {
            $this->logger->critical($exception);
            // phpcs:ignore Magento2.Security.IncludeFile
            require $this->filesystem
                ->getDirectoryRead(DirectoryList::PUB)
                ->getAbsolutePath('errors/404.php');
            return true;
        }
        return false;
    }

    /**
     * Handle for any other errors
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @return bool
     */
    private function handleGenericReport(Bootstrap $bootstrap, \Exception $exception): bool
    {
        $reportData = [
            $exception->getMessage(),
            Debug::trace(
                $exception->getTrace(),
                true,
                false,
                (bool)getenv('MAGE_DEBUG_SHOW_ARGS')
            )
        ];
        $params = $bootstrap->getParams();
        if (isset($params['REQUEST_URI'])) {
            $reportData['url'] = $params['REQUEST_URI'];
        }
        if (isset($params['SCRIPT_NAME'])) {
            $reportData['script_name'] = $params['SCRIPT_NAME'];
        }
        $reportData['report_id'] = $this->encryptor->getHash(implode('', $reportData));
        $this->logger->critical($exception, ['report_id' => $reportData['report_id']]);
        // phpcs:ignore Magento2.Security.IncludeFile
        require $this->filesystem
            ->getDirectoryRead(DirectoryList::PUB)
            ->getAbsolutePath('errors/report.php');
        return true;
    }

    /**
     * If not installed, try to redirect to installation wizard
     *
     * @param Bootstrap $bootstrap
     * @param \Exception $exception
     * @param ResponseHttp $response
     * @return void
     * @throws \Exception
     */
    private function redirectToSetup(Bootstrap $bootstrap, \Exception $exception, ResponseHttp $response)
    {
        $setupInfo = new SetupInfo($bootstrap->getParams());
        $projectRoot = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
        if ($setupInfo->isAvailable()) {
            $response->setRedirect($setupInfo->getUrl());
            $response->sendHeaders();
        } else {
            $newMessage = $exception->getMessage() . "\nNOTE: You cannot install Magento using the Setup Wizard "
                . "because the Magento setup directory cannot be accessed. \n"
                . 'You can install Magento using either the command line or you must restore access '
                . 'to the following directory: ' . $setupInfo->getDir($projectRoot) . "\n";
            // phpcs:ignore Magento2.Exceptions.DirectThrow
            throw new \Exception($newMessage, 0, $exception);
        }
    }
}
