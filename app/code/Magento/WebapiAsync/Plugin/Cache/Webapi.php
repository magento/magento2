<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\WebapiAsync\Plugin\Cache;

use Magento\WebapiAsync\Controller\Rest\AsynchronousSchemaRequestProcessor;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Class Webapi
 */
class Webapi
{
    /**
     * Cache key for Async Routes
     */
    const ASYNC_ROUTES_CONFIG_CACHE_ID = 'async-routes-services-config';

    /**
     * @var AsynchronousSchemaRequestProcessor
     */
    private $asynchronousSchemaRequestProcessor;

    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $request;

    /**
     * ServiceMetadata constructor.
     *
     * @param Request $request
     * @param AsynchronousSchemaRequestProcessor $asynchronousSchemaRequestProcessor
     */
    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        AsynchronousSchemaRequestProcessor $asynchronousSchemaRequestProcessor
    ) {
        $this->request = $request;
        $this->asynchronousSchemaRequestProcessor = $asynchronousSchemaRequestProcessor;
    }

    /**
     * Change identifier in case if Async request before cache load
     *
     * @param \Magento\Webapi\Model\Cache\Type\Webapi $subject
     * @param string $identifier
     * @return null|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeLoad(\Magento\Webapi\Model\Cache\Type\Webapi $subject, $identifier)
    {
        if ($this->asynchronousSchemaRequestProcessor->canProcess($this->request)
            && $identifier === \Magento\Webapi\Model\ServiceMetadata::ROUTES_CONFIG_CACHE_ID) {
            return self::ASYNC_ROUTES_CONFIG_CACHE_ID;
        }
        return null;
    }

    /**
     * Change identifier in case if Async request before cache save
     *
     * @param \Magento\Webapi\Model\Cache\Type\Webapi $subject
     * @param string $data
     * @param string $identifier
     * @param array $tags
     * @param int|bool|null $lifeTime
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Webapi\Model\Cache\Type\Webapi $subject,
        $data,
        $identifier,
        array $tags = [],
        $lifeTime = null
    ) {
        if ($this->asynchronousSchemaRequestProcessor->canProcess($this->request)
            && $identifier === \Magento\Webapi\Model\ServiceMetadata::ROUTES_CONFIG_CACHE_ID) {
            return [$data, self::ASYNC_ROUTES_CONFIG_CACHE_ID, $tags, $lifeTime];
        }
        return null;
    }

    /**
     * Change identifier in case if Async request before remove cache
     *
     * @param \Magento\Webapi\Model\Cache\Type\Webapi $subject
     * @param string $identifier
     * @return null|string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeRemove(\Magento\Webapi\Model\Cache\Type\Webapi $subject, $identifier)
    {
        if ($this->asynchronousSchemaRequestProcessor->canProcess($this->request)
            && $identifier === \Magento\Webapi\Model\ServiceMetadata::ROUTES_CONFIG_CACHE_ID) {
            return self::ASYNC_ROUTES_CONFIG_CACHE_ID;
        }
        return null;
    }
}
