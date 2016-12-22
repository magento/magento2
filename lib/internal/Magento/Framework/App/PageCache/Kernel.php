<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Builtin cache processor
 */
class Kernel
{
    /**
     * @var \Magento\PageCache\Model\Cache\Type
     *
     * @deprecated
     */
    protected $cache;

    /**
     * @var Identifier
     */
    protected $identifier;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\PageCache\Model\Cache\Type
     */
    private $fullPageCache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @param Cache $cache
     * @param Identifier $identifier
     * @param \Magento\Framework\App\Request\Http $request
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\PageCache\Cache $cache,
        \Magento\Framework\App\PageCache\Identifier $identifier,
        \Magento\Framework\App\Request\Http $request,
        SerializerInterface $serializer = null
    ) {
        $this->cache = $cache;
        $this->identifier = $identifier;
        $this->request = $request;
        $this->serializer = $serializer ?: $this->getObjectManager()->get(SerializerInterface::class);
    }

    /**
     * Load response from cache
     *
     * @return \Magento\Framework\App\Response\Http|false
     */
    public function load()
    {
        if ($this->request->isGet() || $this->request->isHead()) {
            $responseData = $this->serializer->unserialize($this->getCache()->load($this->identifier->getValue()));
            if (!$responseData) {
                return false;
            }

            $context = $this->getObjectManager()->create(
                \Magento\Framework\App\Http\Context::class,
                [
                    'data' => $responseData['context']['data'],
                    'default' => $responseData['context']['default']
                ]
            );

            $response = $this->getObjectManager()->create(
                \Magento\Framework\App\Response\Http::class,
                [
                    'context' => $context
                ]
            );
            $response->setStatusCode($responseData['status_code']);
            $response->setContent($responseData['content']);
            foreach ($responseData['headers'] as $headerKey => $headerValue) {
                $response->setHeader($headerKey, $headerValue, true);
            }
            return $response;
        }
        return false;
    }

    /**
     * Modify and cache application response
     *
     * @param \Magento\Framework\App\Response\Http $response
     * @return void
     */
    public function process(\Magento\Framework\App\Response\Http $response)
    {
        if (preg_match('/public.*s-maxage=(\d+)/', $response->getHeader('Cache-Control')->getFieldValue(), $matches)) {
            $maxAge = $matches[1];
            $response->setNoCacheHeaders();
            if (($response->getHttpResponseCode() == 200 || $response->getHttpResponseCode() == 404)
                && ($this->request->isGet() || $this->request->isHead())
            ) {
                $tagsHeader = $response->getHeader('X-Magento-Tags');
                $tags = $tagsHeader ? explode(',', $tagsHeader->getFieldValue()) : [];

                $response->clearHeader('Set-Cookie');
                $response->clearHeader('X-Magento-Tags');
                if (!headers_sent()) {
                    header_remove('Set-Cookie');
                }

                $this->getCache()->save(
                    $this->serializer->serialize($this->cacheDataPreparation($response)),
                    $this->identifier->getValue(),
                    $tags,
                    $maxAge
                );
            }
        }
    }

    /*
     * Preparation data for storage in the cache.
     *
     * @param \Magento\Framework\App\Response\Http $response
     * @return array
     */
    private function cacheDataPreparation(\Magento\Framework\App\Response\Http $response)
    {
        $context = $this->getObjectManager()->get(\Magento\Framework\App\Http\Context::class);

        return [
            'content' => $response->getContent(),
            'status_code' => $response->getStatusCode(),
            'headers' => $response->getHeaders()->toArray(),
            'context' => $context->toArray()
        ];

    }

    /**
     * Get ObjectManager Instance
     *
     * @return ObjectManager
     */
    private function getObjectManager()
    {
        if ($this->objectManager === null) {
            $this->objectManager = ObjectManager::getInstance();
        }
        return $this->objectManager;
    }

    /**
     * TODO: Workaround to support backwards compatibility, will rework to use Dependency Injection in MAGETWO-49547
     *
     * @return \Magento\PageCache\Model\Cache\Type
     */
    private function getCache()
    {
        if (!$this->fullPageCache) {
            $this->fullPageCache = $this->getObjectManager()->get(\Magento\PageCache\Model\Cache\Type::class);
        }
        return $this->fullPageCache;
    }
}
