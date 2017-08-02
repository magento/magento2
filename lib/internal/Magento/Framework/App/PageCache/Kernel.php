<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

/**
 * Builtin cache processor
 * @since 2.0.0
 */
class Kernel
{
    /**
     * @var \Magento\PageCache\Model\Cache\Type
     *
     * @deprecated 2.1.0
     * @since 2.0.0
     */
    protected $cache;

    /**
     * @var Identifier
     * @since 2.0.0
     */
    protected $identifier;

    /**
     * @var \Magento\Framework\App\Request\Http
     * @since 2.0.0
     */
    protected $request;

    /**
     * @var \Magento\PageCache\Model\Cache\Type
     * @since 2.1.0
     */
    private $fullPageCache;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @var \Magento\Framework\App\Http\Context
     * @since 2.2.0
     */
    private $context;

    /**
     * @var \Magento\Framework\App\Http\ContextFactory
     * @since 2.2.0
     */
    private $contextFactory;

    /**
     * @var \Magento\Framework\App\Response\HttpFactory
     * @since 2.2.0
     */
    private $httpFactory;

    /**
     * @param Cache $cache
     * @param Identifier $identifier
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context|null $context
     * @param \Magento\Framework\App\Http\ContextFactory|null $contextFactory
     * @param \Magento\Framework\App\Response\HttpFactory|null $httpFactory
     * @param \Magento\Framework\Serialize\SerializerInterface|null $serializer
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\PageCache\Cache $cache,
        \Magento\Framework\App\PageCache\Identifier $identifier,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Http\Context $context = null,
        \Magento\Framework\App\Http\ContextFactory $contextFactory = null,
        \Magento\Framework\App\Response\HttpFactory $httpFactory = null,
        \Magento\Framework\Serialize\SerializerInterface $serializer = null
    ) {
        $this->cache = $cache;
        $this->identifier = $identifier;
        $this->request = $request;

        if ($context) {
            $this->context = $context;
        } else {
            $this->context = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Http\Context::class
            );
        }
        if ($contextFactory) {
            $this->contextFactory = $contextFactory;
        } else {
            $this->contextFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Http\ContextFactory::class
            );
        }
        if ($httpFactory) {
            $this->httpFactory = $httpFactory;
        } else {
            $this->httpFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Response\HttpFactory::class
            );
        }
        if ($serializer) {
            $this->serializer = $serializer;
        } else {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Serialize\SerializerInterface::class
            );
        }
    }

    /**
     * Load response from cache
     *
     * @return \Magento\Framework\App\Response\Http|false
     * @since 2.0.0
     */
    public function load()
    {
        if ($this->request->isGet() || $this->request->isHead()) {
            $responseData = $this->getCache()->load($this->identifier->getValue());
            if (!$responseData) {
                return false;
            }
            $responseData = $this->serializer->unserialize($responseData);
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
     * @since 2.0.0
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
     * @since 2.2.0
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
     * @since 2.2.0
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
     * @since 2.1.0
     */
    private function getCache()
    {
        if (!$this->fullPageCache) {
            $this->fullPageCache = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\PageCache\Model\Cache\Type::class
            );
        }
        return $this->fullPageCache;
    }
}
