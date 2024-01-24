<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;

/**
 * Format attribute values provider for GraphQL output
 */
class GetAttributeValueComposite implements GetAttributeValueInterface
{
    /**
     * @var GetAttributeValueInterface[]
     */
    private array $providers;

    /**
     * @param array $providers
     */
    public function __construct(array $providers = [])
    {
        $this->providers = $providers;
    }

    /**
     * Returns right GetAttributeValueInterface to use for attribute with $attributeCode
     *
     * @param string $entityType
     * @param array $customAttribute
     * @return array|null
     * @throws RuntimeException|LocalizedException
     */
    public function execute(string $entityType, array $customAttribute): ?array
    {
        if (!isset($this->providers[$entityType])) {
            throw new RuntimeException(
                __(sprintf('"%s" entity type not set in providers', $entityType))
            );
        }
        if (!$this->providers[$entityType] instanceof GetAttributeValueInterface) {
            throw new RuntimeException(
                __('Configured attribute data providers should implement GetAttributeValueInterface')
            );
        }

        return $this->providers[$entityType]->execute($entityType, $customAttribute);
    }
}
