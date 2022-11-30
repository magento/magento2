<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree\Wrapper;

use Magento\Catalog\Model\Category;

/**
 * Category tree node wrapper.
 */
class Node
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var self[]
     */
    private $children = [];

    /**
     * @var Category
     */
    private $model;

    /**
     * @var Forgery
     */
    private $forgery;

    /**
     * @param int $id
     * @param Forgery $forgery
     */
    public function __construct(int $id, Forgery $forgery)
    {
        $this->id = $id;
        $this->forgery = $forgery;
    }

    /**
     * Set category model for node.
     *
     * @param Category $category
     * @return $this
     */
    public function setModel(Category $category): self
    {
        $this->model = $category;
        return $this;
    }

    /**
     * Add child node.
     *
     * @param Node $categoryTreeNode
     * @return $this
     */
    public function addChild(self $categoryTreeNode): self
    {
        $this->children[$categoryTreeNode->getId()] = $categoryTreeNode;
        return $this;
    }

    /**
     * Get array of children nodes.
     *
     * @return Node[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * Get category model for node.
     *
     * @return Category
     */
    public function getModel(): Category
    {
        return $this->model;
    }

    /**
     * Get node id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Render node and its children as an array recursively.
     *
     * @return array
     */
    public function renderArray(): array
    {
        return array_merge(
            $this->forgery->getHydrator()->hydrateCategory($this->model),
            [
                'children' => array_map(
                    function ($node) {
                        return $node->renderArray();
                    },
                    $this->children
                )
            ]
        );
    }
}
