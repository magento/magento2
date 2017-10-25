<?php
/**
 * Web API REST response.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\GraphQl;

class Response extends \Magento\Framework\Webapi\Response
{
    /**
     * @var \Magento\Framework\Webapi\ErrorProcessor
     */
    private $errorProcessor;

    /**
     * @var \Magento\Framework\Webapi\Rest\Response\RendererInterface
     */
    private $renderer;

    /**
     * @var \Magento\Framework\App\State
     */
    private $appState;

    /**
     * Exception stack
     * @var \Exception[]
     */
    private $exceptions = [];

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
        $this->renderer = $rendererFactory->get();
        $this->errorProcessor = $errorProcessor;
        $this->appState = $appState;
    }

    /**
     * Send response to the client, render exceptions if they are present.
     *
     * @return void
     */
    public function sendResponse()
    {
        try {
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
            $this->errorProcessor->renderException($e, $httpCode);
        }
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
}
