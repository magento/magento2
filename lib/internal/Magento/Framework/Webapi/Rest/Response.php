<?php
/**
 * Web API REST response.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Rest;

class Response extends \Magento\Framework\Webapi\Response
{
    /** @var \Magento\Framework\Webapi\ErrorProcessor */
    protected $_errorProcessor;

    /** @var \Magento\Framework\Webapi\Rest\Response\RendererInterface */
    protected $_renderer;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Exception stack
     * @var \Exception
     */
    protected $exceptions = [];

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Webapi\Rest\Response\RendererFactory $rendererFactory
     * @param \Magento\Framework\Webapi\ErrorProcessor $errorProcessor
     * @param \Magento\Framework\App\State $appState
     */
    public function __construct(
        \Magento\Framework\Webapi\Rest\Response\RendererFactory $rendererFactory,
        \Magento\Framework\Webapi\ErrorProcessor $errorProcessor,
        \Magento\Framework\App\State $appState
    ) {
        $this->_renderer = $rendererFactory->get();
        $this->_errorProcessor = $errorProcessor;
        $this->_appState = $appState;
    }

    /**
     * Send response to the client, render exceptions if they are present.
     *
     * @return void
     */
    public function sendResponse()
    {
        try {
            if ($this->isException()) {
                $this->_renderMessages();
            }
            parent::sendResponse();
        } catch (\Exception $e) {
            if ($e instanceof \Magento\Framework\Webapi\Exception) {
                // If the server does not support all MIME types accepted by the client it SHOULD send 406.
                $httpCode = $e->getHttpCode() ==
                    \Magento\Framework\Webapi\Exception::HTTP_NOT_ACCEPTABLE ?
                    \Magento\Framework\Webapi\Exception::HTTP_NOT_ACCEPTABLE :
                    \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR;
            } else {
                $httpCode = \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR;
            }

            /** If error was encountered during "error rendering" process then use error renderer. */
            $this->_errorProcessor->renderException($e, $httpCode);
        }
    }

    /**
     * Generate and set HTTP response code, error messages to Response object.
     *
     * @return $this
     */
    protected function _renderMessages()
    {
        $responseHttpCode = null;
        /** @var \Exception $exception */
        foreach ($this->getException() as $exception) {
            $maskedException = $this->_errorProcessor->maskException($exception);
            $messageData = [
                'message' => $maskedException->getMessage(),
            ];
            if ($maskedException->getErrors()) {
                $messageData['errors'] = [];
                foreach ($maskedException->getErrors() as $errorMessage) {
                    $errorData['message'] = $errorMessage->getRawMessage();
                    $errorData['parameters'] = $errorMessage->getParameters();
                    $messageData['errors'][] = $errorData;
                }
            }
            if ($maskedException->getCode()) {
                $messageData['code'] = $maskedException->getCode();
            }
            if ($maskedException->getDetails()) {
                $messageData['parameters'] = $maskedException->getDetails();
            }
            if ($this->_appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
                $messageData['trace'] = $exception instanceof \Magento\Framework\Webapi\Exception
                    ? $exception->getStackTrace()
                    : $exception->getTraceAsString();
            }
            $responseHttpCode = $maskedException->getHttpCode();
        }
        // set HTTP code of the last error, Content-Type, and all rendered error messages to body
        $this->setHttpResponseCode($responseHttpCode);
        $this->setMimeType($this->_renderer->getMimeType());
        $this->setBody($this->_renderer->render($messageData));
        return $this;
    }

    /**
     * Perform rendering of response data.
     *
     * @param array|int|string|bool|float|null $outputData
     * @return $this
     */
    public function prepareResponse($outputData = null)
    {
        $this->_render($outputData);
        if ($this->getMessages()) {
            $this->_render(['messages' => $this->getMessages()]);
        }
        return $this;
    }

    /**
     * Render data using registered Renderer.
     *
     * @param array|int|string|bool|float|null $data
     * @return void
     */
    protected function _render($data)
    {
        $mimeType = $this->_renderer->getMimeType();
        $body = $this->_renderer->render($data);
        $this->setMimeType($mimeType)->setBody($body);
    }

    /**
     * Register an exception with the response
     *
     * @param \Exception $e
     * @return $this
     */
    public function setException($e)
    {
        $this->exceptions[] = $e;
        return $this;
    }

    /**
     * Has an exception been registered with the response?
     *
     * @return boolean
     */
    public function isException()
    {
        return !empty($this->exceptions);
    }

    /**
     * Retrieve the exception stack
     *
     * @return array
     */
    public function getException()
    {
        return $this->exceptions;
    }

    /**
     * Does the response object contain an exception of a given type?
     *
     * @param  string $type
     * @return boolean
     */
    public function hasExceptionOfType($type)
    {
        foreach ($this->exceptions as $e) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }
}
