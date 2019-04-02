<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\GraphQl;

use Magento\Framework\App\FrontControllerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\GraphQlCache\Model\CacheTags;
use Magento\Framework\App\State as AppState;

/**
 * Class Plugin

 * @package Magento\GraphQlCache\Controller\GraphQl
 */
class Plugin
{
    /**
     * @var CacheTags
     */
    private $cacheTags;

    /**
     * @var AppState
     */
    private $state;

    /**
     * Constructor
     *
     * @param CacheTags $cacheTags
     * @param AppState $state
     */
    public function __construct(CacheTags $cacheTags, AppState $state)
    {
        $this->cacheTags = $cacheTags;
        $this->state = $state;
    }

    /**
     * Plugin for GraphQL Controller
     *
     * @param FrontControllerInterface $subject
     * @param ResponseInterface $response
     * @param RequestInterface $request
     * @return ResponseInterface|\Magento\Framework\Webapi\Response
     */
    public function afterDispatch(
        FrontControllerInterface $subject,
        /* \Magento\Framework\App\Response\Http */ $response,
        RequestInterface $request
    ) {
        /** @var \Magento\Framework\App\Request\Http $request */
        /** @var \Magento\Framework\App\Response\Http $response */
        $cacheTags = $this->cacheTags->getCacheTags();
        if (count($cacheTags)) {
            // assume that response should be cacheable if it contains cache tags
            $response->setHeader('Pragma', 'cache', true);
            // TODO: Take from configuration
            $response->setHeader('Cache-Control', 'max-age=86400, public, s-maxage=86400', true);
            $response->setHeader('X-Magento-Tags', implode(',', $cacheTags), true);
        }

        if ($request->isGet() && $this->state->getMode() == AppState::MODE_DEVELOPER) {
            $response->setHeader('X-Magento-Debug', 1);
        }

        return $response;
    }
}
