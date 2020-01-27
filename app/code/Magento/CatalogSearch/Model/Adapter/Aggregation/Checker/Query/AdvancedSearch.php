<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Adapter\Aggregation\Checker\Query;

use Magento\CatalogSearch\Model\Adapter\Aggregation\RequestCheckerInterface;
use Magento\Framework\Search\RequestInterface;

/**
 * Request checker for advanced search.
 *
 * Checks advanced search query whether required to collect all attributes for entity.
 */
class AdvancedSearch implements RequestCheckerInterface
{
    /**
     * Identifier for query name
     */
    private $name;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function isApplicable(RequestInterface $request)
    {
        $result = true;
        // It's no need to render LN filters for advanced search
        if ($request->getName() === $this->name) {
            $result = false;
        }

        return $result;
    }
}
