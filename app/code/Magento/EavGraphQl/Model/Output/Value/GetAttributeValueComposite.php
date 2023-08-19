<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Output\Value;

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
     * @inheritdoc
     */
    public function execute(string $entity, string $code, string $value): ?array
    {
        foreach ($this->providers as $provider) {
            if (!$provider instanceof GetAttributeValueInterface) {
                throw new RuntimeException(
                    __('Configured attribute data providers should implement GetAttributeValueInterface')
                );
            }

            try {
                return $provider->execute($entity, $code, $value);
            } catch (LocalizedException $e) {
                continue;
            }
        }
        return null;
    }
}
