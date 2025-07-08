<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Output\Value\Options;

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
     * @inheritdoc
     */
    public function execute(string $entity, string $code, string $value): ?array
    {
        foreach ($this->providers as $provider) {
            if (!$provider instanceof GetAttributeSelectedOptionInterface) {
                throw new RuntimeException(
                    __('Configured attribute selected option data providers should implement
                    GetAttributeSelectedOptionInterface')
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
