<?php
/**
 * Web API REST response.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Controller\Rest;

class Response extends \Magento\Webapi\Controller\Response
{
    /** @var \Magento\Webapi\Controller\ErrorProcessor */
    protected $_errorProcessor;

    /** @var \Magento\Webapi\Controller\Rest\Response\RendererInterface */
    protected $_renderer;

    /** @var \Magento\Core\Model\App */
    protected $_app;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Webapi\Controller\Rest\Response\Renderer\Factory $rendererFactory
     * @param \Magento\Webapi\Controller\ErrorProcessor $errorProcessor
     * @param \Magento\Core\Model\App $app
     */
    public function __construct(
        \Magento\Webapi\Controller\Rest\Response\Renderer\Factory $rendererFactory,
        \Magento\Webapi\Controller\ErrorProcessor $errorProcessor,
        \Magento\Core\Model\App $app
    ) {
        $this->_renderer = $rendererFactory->get();
        $this->_errorProcessor = $errorProcessor;
        $this->_app = $app;
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
            if ($e instanceof \Magento\Webapi\Exception) {
                // If the server does not support all MIME types accepted by the client it SHOULD send 406.
                $httpCode = $e->getHttpCode() == \Magento\Webapi\Exception::HTTP_NOT_ACCEPTABLE
                    ? \Magento\Webapi\Exception::HTTP_NOT_ACCEPTABLE
                    : \Magento\Webapi\Exception::HTTP_INTERNAL_ERROR;
            } else {
                $httpCode = \Magento\Webapi\Exception::HTTP_INTERNAL_ERROR;
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
        $formattedMessages = $this->getMessages();
        $responseHttpCode = null;
        /** @var \Exception $exception */
        foreach ($this->getException() as $exception) {
            $maskedException = $this->_errorProcessor->maskException($exception);
            $messageData = array(
                'message' => $maskedException->getMessage(),
                'http_code' => $maskedException->getHttpCode()
            );
            if ($maskedException->getCode()) {
                $messageData['code'] = $maskedException->getCode();
            }
            if ($maskedException->getDetails()) {
                $messageData['parameters'] = $maskedException->getDetails();
            }
            if ($this->_app->isDeveloperMode()) {
                $messageData['trace'] = $exception->getTraceAsString();
            }
            $formattedMessages['errors'][] = $messageData;
            $responseHttpCode = $maskedException->getHttpCode();
        }
        // set HTTP code of the last error, Content-Type, and all rendered error messages to body
        $this->setHttpResponseCode($responseHttpCode);
        $this->setMimeType($this->_renderer->getMimeType());
        $this->setBody($this->_renderer->render($formattedMessages));
        return $this;
    }

    /**
     * Perform rendering of response data.
     *
     * @param array|null $outputData
     * @return $this
     */
    public function prepareResponse($outputData = null)
    {
        $this->_render($outputData);
        if ($this->getMessages()) {
            $this->_render(array('messages' => $this->getMessages()));
        };
        return $this;
    }

    /**
     * Render data using registered Renderer.
     *
     * @param array|object $data
     * @return void
     */
    protected function _render($data)
    {
        $mimeType = $this->_renderer->getMimeType();
        $body = $this->_renderer->render($data);
        $this->setMimeType($mimeType)->setBody($body);
    }
}
