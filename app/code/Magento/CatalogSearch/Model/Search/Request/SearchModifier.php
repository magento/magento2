<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search\Request;

use Magento\CatalogSearch\Model\Search\RequestGenerator;

/**
 * Modifies search requests configuration
 */
class SearchModifier implements ModifierInterface
{
    /**
     * @var RequestGenerator
     */
    private $requestGenerator;

    /**
     * @param RequestGenerator $requestGenerator
     */
    public function __construct(
        RequestGenerator $requestGenerator
    ) {
        $this->requestGenerator = $requestGenerator;
    }

    /**
     * @inheritdoc
     */
    public function modify(array $requests): array
    {
        $requests = array_merge_recursive($requests, $this->requestGenerator->generate());
        return $requests;
    }
}
