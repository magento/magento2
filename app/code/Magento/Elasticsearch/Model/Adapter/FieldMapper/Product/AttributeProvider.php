<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product;

use Magento\Eav\Model\Config;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter\DummyAttribute;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Provide attribute adapter.
 */
class AttributeProvider implements ResetAfterRequestInterface
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private ObjectManagerInterface $objectManager;

    /**
     * Instance name to create
     *
     * @var string
     */
    private string $instanceName;

    /**
     * @var Config
     */
    private Config $eavConfig;

    /**
     * @var array
     */
    private array $cachedPool = [];

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param Config $eavConfig
     * @param LoggerInterface $logger
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Config $eavConfig,
        LoggerInterface $logger,
        $instanceName = AttributeAdapter::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
        $this->eavConfig = $eavConfig;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function getByAttributeCode(string $attributeCode): AttributeAdapter
    {
        if (!isset($this->cachedPool[$attributeCode])) {
            $attribute = $this->eavConfig->getAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                $attributeCode
            );
            if (null === $attribute) {
                $attribute = $this->objectManager->create(DummyAttribute::class);
            }
            $this->cachedPool[$attributeCode] = $this->objectManager->create(
                $this->instanceName,
                ['attribute' => $attribute, 'attributeCode' => $attributeCode]
            );
        }

        return $this->cachedPool[$attributeCode];
    }

    /**
     * Remove attribute from cache by code.
     *
     * @param string $attributeCode
     * @return void
     */
    public function removeAttributeCacheByCode(string $attributeCode): void
    {
        if (isset($this->cachedPool[$attributeCode])) {
            unset($this->cachedPool[$attributeCode]);
        }
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->cachedPool = [];
    }
}
