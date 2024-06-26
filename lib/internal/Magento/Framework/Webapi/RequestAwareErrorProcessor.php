<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Webapi;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\HTTP\PhpEnvironment\Response;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Request dependent Error Processor
 */
class RequestAwareErrorProcessor extends ErrorProcessor
{
    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var Response
     */
    private Response $response;

    /**
     * @param \Magento\Framework\Json\Encoder $encoder
     * @param \Magento\Framework\App\State $appState
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param Json|null $serializer
     * @param Request|null $request
     * @param Response|null $response
     */
    public function __construct(
        \Magento\Framework\Json\Encoder $encoder,
        \Magento\Framework\App\State $appState,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        Json $serializer = null,
        Request $request = null,
        Response $response = null
    ) {
        $this->request = $request ?: ObjectManager::getInstance()->get(Request::class);
        $this->response = $response ?: ObjectManager::getInstance()->get(Response::class);
        parent::__construct(
            $encoder,
            $appState,
            $logger,
            $filesystem,
            $serializer
        );
    }

    /**
     * @inheritDoc
     */
    public function renderErrorMessage(
        $errorMessage,
        $trace = 'Trace is not available.',
        $httpCode = self::DEFAULT_ERROR_HTTP_CODE
    ) {
        if (isset($this->request->getServer()['HTTP_ACCEPT']) &&
            strstr($this->request->getServer()['HTTP_ACCEPT'], self::DATA_FORMAT_XML)) {
            $output = $this->_formatError($errorMessage, $trace, $httpCode, self::DATA_FORMAT_XML);
            $mimeType = 'application/xml';
        } else {
            // Default format is JSON
            $output = $this->_formatError($errorMessage, $trace, $httpCode, self::DATA_FORMAT_JSON);
            $mimeType = 'application/json';
        }
        if (!headers_sent()) {
            $this->response->setStatusCode($httpCode ? $httpCode : self::DEFAULT_ERROR_HTTP_CODE);
            $this->response->getHeaders()->addHeaderLine(
                'Content-Type: ' . $mimeType . '; charset=' . self::DEFAULT_RESPONSE_CHARSET
            );
        }
        // phpcs:ignore Magento2.Security.LanguageConstruct.DirectOutput
        echo $output;
    }
}
