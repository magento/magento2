<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\Http\ContextFactory;
use Magento\Framework\App\Response\HttpFactory;

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
     * @var Context
     */
    private $context;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * @var HttpFactory
     */
    private $httpFactory;

    /**
     * @param Cache $cache
     * @param Identifier $identifier
     * @param \Magento\Framework\App\Request\Http $request
     * @param Context|null $context
     * @param ContextFactory|null $contextFactory
     * @param HttpFactory|null $httpFactory
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\PageCache\Cache $cache,
        \Magento\Framework\App\PageCache\Identifier $identifier,
        \Magento\Framework\App\Request\Http $request,
        Context $context = null,
        ContextFactory $contextFactory = null,
        HttpFactory $httpFactory = null,
        SerializerInterface $serializer = null
    ) {
        $this->cache = $cache;
        $this->identifier = $identifier;
        $this->request = $request;
        $this->context = $context ?: ObjectManager::getInstance()->get(Context::class);
        $this->contextFactory = $contextFactory ?: ObjectManager::getInstance()->get(ContextFactory::class);
        $this->httpFactory = $httpFactory ?: ObjectManager::getInstance()->get(HttpFactory::class);
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
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

            return $this->buildResponse($responseData);
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
                    $this->serializer->serialize($this->getPreparedData($response)),
                    $this->identifier->getValue(),
                    $tags,
                    $maxAge
                );
            }
        }
    }

    /**
     * Get prepared data for storage in the cache.
     *
     * @param \Magento\Framework\App\Response\Http $response
     * @return array
     */
    private function getPreparedData(\Magento\Framework\App\Response\Http $response)
    {
        return [
            'content' => $response->getContent(),
            'status_code' => $response->getStatusCode(),
            'headers' => $response->getHeaders()->toArray(),
            'context' => $this->context->toArray()
        ];

    }

    /**
     * Build response using response data.
     *
     * @param array $responseData
     * @return \Magento\Framework\App\Response\Http
     */
    private function buildResponse($responseData)
    {
        $context = $this->contextFactory->create(
            [
                'data' => $responseData['context']['data'],
                'default' => $responseData['context']['default']
            ]
        );

        $response = $this->httpFactory->create(
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

    /**
     * TODO: Workaround to support backwards compatibility, will rework to use Dependency Injection in MAGETWO-49547
     *
     * @return \Magento\PageCache\Model\Cache\Type
     */
    private function getCache()
    {
        if (!$this->fullPageCache) {
            $this->fullPageCache = ObjectManager::getInstance()->get(\Magento\PageCache\Model\Cache\Type::class);
        }
        return $this->fullPageCache;
    }
}
