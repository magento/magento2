<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Model\Output;

use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\Exception\RuntimeException;

/**
 * Format attributes for GraphQL output
 */
class GetAttributeDataComposite implements GetAttributeDataInterface
{
    /**
     * @var GetAttributeDataInterface[]
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
     * Retrieve formatted attribute data
     *
     * @param AttributeInterface $attribute
     * @param string $entityType
     * @param int $storeId
     * @return array
     * @throws RuntimeException
     */
    public function execute(
        AttributeInterface $attribute,
        string $entityType,
        int $storeId
    ): array {
        $data = [];

        foreach ($this->providers as $provider) {
            if (!$provider instanceof GetAttributeDataInterface) {
                throw new RuntimeException(
                    __('Configured attribute data providers should implement GetAttributeDataInterface')
                );
            }
            $data[] = $provider->execute($attribute, $entityType, $storeId);
        }

        return array_merge([], ...$data);
    }
}
