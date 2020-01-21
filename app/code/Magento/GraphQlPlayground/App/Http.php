<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlPlayground\App;

use Magento\Framework\App\Area;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Http
 *
 * @package Magento\GraphQlPlayground\App
 */
class Http extends \Magento\Framework\App\Http
{
    /**
     * Should render playground
     *
     * @return bool
     */
    private function shouldRenderPlayground(): bool
    {
        $shouldRenderPlayground = false;
        if (!$this->_request->getParam('query') && $this->_request->isGet()) {
            $shouldRenderPlayground = true;
        }
        return $shouldRenderPlayground;
    }

    /**
     * Run application
     *
     * @return ResponseInterface
     * @throws LocalizedException|\InvalidArgumentException
     */
    public function launch()
    {
        $graphQlFrontName = $this->_areaList->getFrontName(Area::AREA_GRAPHQL);
        if ($this->_request->getFrontName() === $graphQlFrontName && $this->shouldRenderPlayground()) {
            $areaCode = $this->_areaList->getCodeByFrontName(Area::AREA_FRONTEND);
        } else {
            $areaCode = $this->_areaList->getCodeByFrontName($this->_request->getFrontName());
        }
        $this->_state->setAreaCode($areaCode);
        $this->_objectManager->configure($this->_configLoader->load($areaCode));
        /** @var \Magento\Framework\App\FrontControllerInterface $frontController */
        $frontController = $this->_objectManager->get(\Magento\Framework\App\FrontControllerInterface::class);
        $result = $frontController->dispatch($this->_request);
        // TODO: Temporary solution until all controllers return ResultInterface (MAGETWO-28359)
        if ($result instanceof ResultInterface) {
            $this->registry->register('use_page_cache_plugin', true, true);
            $result->renderResult($this->_response);
        } elseif ($result instanceof HttpInterface) {
            $this->_response = $result;
        } else {
            throw new \InvalidArgumentException('Invalid return type');
        }
        if ($this->_request->isHead() && $this->_response->getHttpResponseCode() == 200) {
            $this->handleHeadRequest();
        }
        // This event gives possibility to launch something before sending output (allow cookie setting)
        $eventParams = ['request' => $this->_request, 'response' => $this->_response];
        $this->_eventManager->dispatch('controller_front_send_response_before', $eventParams);
        return $this->_response;
    }

    /**
     * Handle HEAD requests by adding the Content-Length header and removing the body from the response.
     *
     * @return void
     */
    private function handleHeadRequest()
    {
        // It is possible that some PHP installations have overloaded strlen to use mb_strlen instead.
        // This means strlen might return the actual number of characters in a non-ascii string instead
        // of the number of bytes. Use mb_strlen explicitly with a single byte character encoding to ensure
        // that the content length is calculated in bytes.
        $contentLength = mb_strlen($this->_response->getContent(), '8bit');
        $this->_response->clearBody();
        $this->_response->setHeader('Content-Length', $contentLength);
    }
}
