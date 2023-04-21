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
     * @param array $customAttribute
     * @return array|null
     * @throws RuntimeException
     */
    public function execute(array $customAttribute): ?array
    {
        foreach ($this->providers as $provider) {
            if (!$provider instanceof GetAttributeValueInterface) {
                throw new RuntimeException(
                    __('Configured attribute data providers should implement GetAttributeValueInterface')
                );
            }

            try {
                return $provider->execute($customAttribute);
            } catch (LocalizedException $e) {
                continue;
            }
        }
        return null;
    }
}
