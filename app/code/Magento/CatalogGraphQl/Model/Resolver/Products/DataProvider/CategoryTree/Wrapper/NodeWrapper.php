<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree\Wrapper;

use Magento\Catalog\Model\Category;
use Magento\CatalogGraphQl\Model\Category\Hydrator;

/**
 * Tree node forgery for category tree wrapper.
 */
class NodeWrapper
{
    /**
     * Most top node id in the tree structure.
     */
    private const TOP_NODE_ID = 0;

    /**
     * Flat index of the tree that stores nodes by entity identifier.
     *
     * @var array
     */
    private array $nodesById = [];

    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @param Hydrator $hydrator
     */
    public function __construct(Hydrator $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * Forge the node and put it into index.
     *
     * @param Category $category
     * @return void
     */
    public function wrap(Category $category): void
    {
        if (!isset($this->nodesById[self::TOP_NODE_ID])) {
            $this->nodesById[self::TOP_NODE_ID] = new Node(self::TOP_NODE_ID);
        }
        $parentId = self::TOP_NODE_ID;
        array_map(
            function ($id) use (&$parentId, $category) {
                $id = (int)$id;
                if (!isset($this->nodesById[$id])) {
                    $this->nodesById[$id] = new Node($id);
                    if ($category->getId() == $id) {
                        $this->nodesById[$id]->setModelData(
                            $this->hydrator->hydrateCategory($category)
                        );
                    }
                    $this->nodesById[$parentId]->addChild($this->nodesById[$id]);
                }
                $parentId = $id;
            },
            explode('/', $category->getPath())
        );
    }

    /**
     * Get node from index by id.
     *
     * @param int $id
     * @return Node|null
     */
    public function getNodeById(int $id) : ?Node
    {
        return $this->nodesById[$id] ?? null;
    }
}
