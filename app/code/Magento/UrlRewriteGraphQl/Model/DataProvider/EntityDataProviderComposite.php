<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\DataProvider;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Composite class for entity data provider
 */
class EntityDataProviderComposite implements EntityDataProviderInterface
{
    /**
     * @var EntityDataProviderInterface[]
     */
    private $dataProviders;

    /**
     * @param EntityDataProviderInterface[] $dataProviders
     */
    public function __construct(array $dataProviders = [])
    {
        $this->dataProviders = $dataProviders;
    }

    /**
     * Get data from provider
     *
     * @param string $entity_type
     * @param int $id
     * @param ResolveInfo|null $info
     * @param int|null $storeId
     * @return array
     */
    public function getData(
        string $entity_type,
        int $id,
        ?ResolveInfo $info = null,
        ?int $storeId = null
    ): array {
        return $this->dataProviders[strtolower($entity_type)]->getData(
            $entity_type,
            $id,
            $info,
            $storeId
        );
    }
}
