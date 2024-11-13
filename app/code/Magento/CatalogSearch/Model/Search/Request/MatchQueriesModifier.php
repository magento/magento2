<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Model\Search\Request;

/**
 * Modifies match queries
 */
class MatchQueriesModifier implements ModifierInterface
{
    /**
     * Queries node name
     */
    private const NODE_QUERIES = 'queries';

    /**
     * Match query node name
     */
    private const NODE_MATCH = 'match';

    /**
     * Match query node field attribute name
     */
    private const NODE_MATCH_ATTRIBUTE_FIELD = 'field';

    /**
     * @var array
     */
    private $queries;

    /**
     * @param array $queries
     */
    public function __construct(array $queries = [])
    {
        $this->queries = $queries;
    }

    /**
     * @inheritdoc
     */
    public function modify(array $requests): array
    {
        foreach ($requests as &$request) {
            foreach ($this->queries as $query => $fields) {
                if (!empty($request[self::NODE_QUERIES][$query][self::NODE_MATCH])) {
                    foreach ($request[self::NODE_QUERIES][$query][self::NODE_MATCH] as $index => $match) {
                        $field = $match[self::NODE_MATCH_ATTRIBUTE_FIELD] ?? null;
                        if ($field !== null && isset($fields[$field])) {
                            $request[self::NODE_QUERIES][$query][self::NODE_MATCH][$index] += $fields[$field];
                        }
                    }
                }
            }
        }
        return $requests;
    }
}
