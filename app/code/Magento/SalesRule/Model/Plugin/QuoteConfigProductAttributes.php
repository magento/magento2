<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\Plugin;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote\Config;
use Magento\SalesRule\Model\ReadRequestFlag;
use Magento\SalesRule\Model\ResourceModel\Rule as RuleResource;
use Magento\SalesRule\Model\Plugin\ResourceModel\Rule as ResourceRulePlugin;

class QuoteConfigProductAttributes
{
    /**
     * @var RuleResource
     */
    private $ruleResource;

    /**
     * @var array|null
     */
    private $activeAttributeCodes;

    /**
     * @param RuleResource $ruleResource
     * @param RequestInterface $request
     * @param ReadRequestFlag $readRequestFlag
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     */
    public function __construct(
        RuleResource $ruleResource,
        private ?RequestInterface $request = null,
        private ?ReadRequestFlag $readRequestFlag = null,
        private ?CacheInterface $cache = null,
        private ?SerializerInterface $serializer = null
    ) {
        $this->ruleResource = $ruleResource;

        $objectManager = ObjectManager::getInstance();

        $this->request = $request
            ?? $objectManager->get(RequestInterface::class);

        $this->readRequestFlag = $readRequestFlag
            ?? $objectManager->get(ReadRequestFlag::class);

        $this->cache = $cache
            ?? $objectManager->get(CacheInterface::class);

        $this->serializer = $serializer
            ?? $objectManager->get(SerializerInterface::class);
    }

    /**
     * Append sales rule product attribute keys to select by quote item collection
     *
     * @param Config $subject
     * @param array $attributeKeys
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetProductAttributes(Config $subject, array $attributeKeys): array
    {
        
        $method = strtoupper($this->request->getMethod());
        $isReadOnly = ($method === 'GET');

        if ($isReadOnly || $this->readRequestFlag->isReadRequest()) {
            return $attributeKeys;
        }

        $cachedData = $this->cache->load(ResourceRulePlugin::CACHE_KEY);

        if ($cachedData !== false) {
            $this->activeAttributeCodes = $this->serializer->unserialize($cachedData);
        } else {
            $this->activeAttributeCodes = array_column(
                $this->ruleResource->getActiveAttributes(),
                'attribute_code'
            );
            $this->cache->save(
                $this->serializer->serialize($this->activeAttributeCodes),
                ResourceRulePlugin::CACHE_KEY,
                [ResourceRulePlugin::CACHE_TAG],
                ResourceRulePlugin::CACHE_TTL
            );
        }

        return array_merge($attributeKeys, $this->activeAttributeCodes);
    }
}
