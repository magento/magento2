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
 * Format selected options values provider for GraphQL output
 */
class GetAttributeSelectedOptionComposite implements GetAttributeSelectedOptionInterface
{
    /**
     * @var GetAttributeSelectedOptionInterface[]
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
     * Returns right GetAttributeSelectedOptionInterface to use for attribute with $attributeCode
     *
     * @param array $customAttribute
     * @return array|null
     * @throws RuntimeException
     */
    public function execute(array $customAttribute): ?array
    {
        foreach ($this->providers as $provider) {
            if (!$provider instanceof GetAttributeSelectedOptionInterface) {
                throw new RuntimeException(
                    __('Configured attribute selected option data providers should implement
                    GetAttributeSelectedOptionInterface')
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
