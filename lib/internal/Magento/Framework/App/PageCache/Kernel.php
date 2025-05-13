<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\PageCache;

use Magento\Framework\App\State as AppState;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\CookieDisablerInterface;

/**
 * Builtin cache processor
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Kernel
{
    /**
     * @var \Magento\PageCache\Model\Cache\Type
     *
     * @deprecated 100.1.0
     * @see Nothing
     */
    protected $cache;

    /**
     * @var \Magento\Framework\App\PageCache\IdentifierInterface
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
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $context;

    /**
     * @var \Magento\Framework\App\Http\ContextFactory
     */
    private $contextFactory;

    /**
     * @var \Magento\Framework\App\Response\HttpFactory
     */
    private $httpFactory;

    /**
     * @var AppState
     */
    private $state;

    /**
     * @var \Magento\Framework\App\PageCache\IdentifierInterface
     */
    private $identifierForSave;

    // phpcs:disable Magento2.Commenting.ClassPropertyPHPDocFormatting
    private readonly CookieDisablerInterface $cookieDisabler;

    /**
     * @param Cache $cache
     * @param \Magento\Framework\App\PageCache\IdentifierInterface $identifier
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Http\Context|null $context
     * @param \Magento\Framework\App\Http\ContextFactory|null $contextFactory
     * @param \Magento\Framework\App\Response\HttpFactory|null $httpFactory
     * @param \Magento\Framework\Serialize\SerializerInterface|null $serializer
     * @param AppState|null $state
     * @param \Magento\PageCache\Model\Cache\Type|null $fullPageCache
     * @param  \Magento\Framework\App\PageCache\IdentifierInterface|null $identifierForSave
     * @param CookieDisablerInterface|null $cookieDisabler
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\PageCache\Cache $cache,
        \Magento\Framework\App\PageCache\IdentifierInterface $identifier,
        \Magento\Framework\App\Request\Http $request,
        ?\Magento\Framework\App\Http\Context $context = null,
        ?\Magento\Framework\App\Http\ContextFactory $contextFactory = null,
        ?\Magento\Framework\App\Response\HttpFactory $httpFactory = null,
        ?\Magento\Framework\Serialize\SerializerInterface $serializer = null,
        ?AppState $state = null,
        ?\Magento\PageCache\Model\Cache\Type $fullPageCache = null,
        ?\Magento\Framework\App\PageCache\IdentifierInterface $identifierForSave = null,
        ?CookieDisablerInterface $cookieDisabler = null,
    ) {
        $this->cache = $cache;
        $this->identifier = $identifier;
        $this->request = $request;
        $this->context = $context ?? ObjectManager::getInstance()->get(\Magento\Framework\App\Http\Context::class);
        $this->contextFactory = $contextFactory ?? ObjectManager::getInstance()->get(
            \Magento\Framework\App\Http\ContextFactory::class
        );
        $this->httpFactory = $httpFactory ?? ObjectManager::getInstance()->get(
            \Magento\Framework\App\Response\HttpFactory::class
        );
        $this->serializer = $serializer ?? ObjectManager::getInstance()->get(
            \Magento\Framework\Serialize\SerializerInterface::class
        );
        $this->state = $state ?? ObjectManager::getInstance()->get(AppState::class);
        $this->fullPageCache = $fullPageCache ?? ObjectManager::getInstance()->get(
            \Magento\PageCache\Model\Cache\Type::class
        );
        $this->identifierForSave = $identifierForSave ?? ObjectManager::getInstance()->get(
            \Magento\Framework\App\PageCache\IdentifierInterface::class
        );
        $this->cookieDisabler = $cookieDisabler ?? ObjectManager::getInstance()->get(CookieDisablerInterface::class);
    }

    /**
     * Load response from cache
     *
     * @return \Magento\Framework\App\Response\Http|false
     */
    public function load()
    {
        if ($this->request->isGet() || $this->request->isHead()) {
            $responseData = $this->fullPageCache->load($this->identifier->getValue());
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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function process(\Magento\Framework\App\Response\Http $response)
    {
        $cacheControlHeader = $response->getHeader('Cache-Control');
        if ($cacheControlHeader
            && preg_match('/public.*s-maxage=(\d+)/', $cacheControlHeader->getFieldValue(), $matches)
        ) {
            $maxAge = $matches[1];
            $response->setNoCacheHeaders();
            if (($response->getHttpResponseCode() == 200 || $response->getHttpResponseCode() == 404)
                && !$response instanceof NotCacheableInterface
                && ($this->request->isGet() || $this->request->isHead())
            ) {
                $tagsHeader = $response->getHeader('X-Magento-Tags');
                $tags = $tagsHeader ? explode(',', $tagsHeader->getFieldValue() ?? '') : [];

                $response->clearHeader('Set-Cookie');
                if ($this->state->getMode() != AppState::MODE_DEVELOPER) {
                    $response->clearHeader('X-Magento-Tags');
                }
                $this->cookieDisabler->setCookiesDisabled(true);

                $this->fullPageCache->save(
                    $this->serializer->serialize($this->getPreparedData($response)),
                    $this->identifierForSave->getValue(),
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
}
