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
class Forgery
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
    private array $indexById = [];

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
    public function forge(Category $category): void
    {
        if (!$this->hasNodeById(self::TOP_NODE_ID)) {
            $this->indexById[self::TOP_NODE_ID] = new Node(self::TOP_NODE_ID);
        }
        $parentId = self::TOP_NODE_ID;
        array_map(
            function ($id) use (&$parentId, $category) {
                $id = (int)$id;
                if (!$this->hasNodeById($id)) {
                    $this->indexById[$id] = new Node($id);
                }
                if ($this->hasNodeById($parentId)) {
                    $this->getNodeById($parentId)->addChild($this->indexById[$id]);
                    if ($category->getId() == $id) {
                        $this->indexById[$id]->setModelData(
                            $this->hydrator->hydrateCategory($category)
                        );
                    }
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
        return $this->indexById[$id] ?? null;
    }

    /**
     * Check whether the node is indexed.
     *
     * @param int $id
     * @return bool
     */
    public function hasNodeById(int $id) : bool
    {
        return isset($this->indexById[$id]);
    }
}
