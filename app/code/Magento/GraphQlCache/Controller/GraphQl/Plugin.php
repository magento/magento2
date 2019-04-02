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
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Controller\ResultInterface;
use Magento\GraphQlCache\Model\CacheTags;
use Magento\PageCache\Model\Config;

/**
 * Class Plugin
 */
class Plugin
{
    /**
     * @var CacheTags
     */
    private $cacheTags;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var HttpResponse
     */
    private $response;

    /**
     * @param CacheTags $cacheTags
     * @param Config $config
     * @param HttpResponse $response
     */
    public function __construct(
        CacheTags $cacheTags,
        Config $config,
        HttpResponse $response
    ) {
        $this->cacheTags = $cacheTags;
        $this->config = $config;
        $this->response = $response;
    }

    /**
     * Plugin for GraphQL after dispatch to set tag and cache headers
     *
     * The $response doesn't have a set type because it's alternating between ResponseInterface and ResultInterface.
     *
     * @param FrontControllerInterface $subject
     * @param ResponseInterface | ResultInterface $response
     * @param RequestInterface $request
     * @return ResponseInterface | ResultInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDispatch(
        FrontControllerInterface $subject,
        $response,
        RequestInterface $request
    ) {
        if ($this->config->isEnabled()) {
            $this->response->setPublicHeaders($this->config->getTtl());
            $cacheTags = $this->cacheTags->getCacheTags();
            if (!empty($cacheTags)) {
                // assume that response should be cacheable if it contains cache tags
                $this->response->setHeader('X-Magento-Tags', implode(',', $cacheTags), true);
            }
        }

        return $response;
    }
}
