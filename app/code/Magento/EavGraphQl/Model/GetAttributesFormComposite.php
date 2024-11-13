<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\RuntimeException;

/**
 * Format attributes form provider for GraphQL output
 */
class GetAttributesFormComposite implements GetAttributesFormInterface
{
    /**
     * @var GetAttributesFormInterface[]
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
     * Returns right GetAttributesFormInterface to use for form with $formCode
     *
     * @param string $formCode
     * @return array
     * @throws RuntimeException
     */
    public function execute(string $formCode): ?array
    {
        foreach ($this->providers as $provider) {
            if (!$provider instanceof GetAttributesFormInterface) {
                throw new RuntimeException(
                    __('Configured attribute data providers should implement GetAttributesFormInterface')
                );
            }

            try {
                return $provider->execute($formCode);
            } catch (LocalizedException $e) {
                continue;
            }
        }
        return null;
    }
}
