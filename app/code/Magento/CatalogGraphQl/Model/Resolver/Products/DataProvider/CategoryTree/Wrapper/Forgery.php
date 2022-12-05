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
        $pathElements = array_map(
            function($element) {
                return (int)$element;
            },
            explode('/', $category->getPath())
        );
        $parentId = null;
        foreach ($pathElements as $id) {
            if ($parentId && $this->hasNodeById($parentId)) {
                if (!$this->hasNodeById($id)) {
                    $this->indexById[$id] = new Node($id, $this);
                    $this->getNodeById($parentId)->addChild($this->indexById[$id]);
                    if ($category->getId() == $id) {
                        $this->indexById[$id]->setModel($category);
                    }
                }
            } elseif (!$this->hasNodeById($id)) {
                $this->indexById[$id] = new Node($id, $this);
            }
            $parentId = $id;
        }
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

    /**
     * Get category hydrator.
     *
     * @return Hydrator
     */
    public function getHydrator()
    {
        return $this->hydrator;
    }
}
