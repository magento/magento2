<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree\Wrapper;

use Magento\Catalog\Model\Category;
use Magento\CatalogGraphQl\Model\Category\Hydrator;
use Magento\Framework\App\ObjectManager;

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
     * Flat index of the tree.
     *
     * @var array
     */
    private array $index = [];

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
        if (!$this->hasNode(self::TOP_NODE_ID)) {
            $this->index[self::TOP_NODE_ID] = new Node(self::TOP_NODE_ID);
        }
        $parentId = self::TOP_NODE_ID;
        array_map(
            function ($id) use (&$parentId, $category) {
                $id = (int)$id;
                if (!$this->hasNode($id)) {
                    $this->index[$id] = new Node($id);
                }
                $this->index[$parentId]->addChild($this->index[$id]);
                if ($category->getId() == $id) {
                    $this->index[$id]->setModelData(
                        $this->hydrator->hydrateCategory($category)
                    );
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
    public function getNode(int $id) : ?Node
    {
        return $this->index[$id] ?? null;
    }

    /**
     * Check whether the node is indexed.
     *
     * @param int $id
     * @return bool
     */
    public function hasNode(int $id) : bool
    {
        return isset($this->index[$id]);
    }
}
