<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\CategoryTree\Wrapper;

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
     * @var array
     */
    private $modelData;

    /**
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * Set category model data for node.
     *
     * @param array|null $modelData
     *
     * @return $this
     */
    public function setModelData(?array $modelData): self
    {
        $this->modelData = $modelData;
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
     * Get node id.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Render node and its children as an array recursively, returns null if node data is not set.
     *
     * @return array|null
     */
    public function renderArray(): ?array
    {
        if (!$this->modelData) {
            return null;
        }
        return array_merge(
            $this->modelData,
            [
                'children' => array_filter(
                    array_map(
                        function ($node) {
                            return $node->renderArray();
                        },
                        $this->children
                    )
                )
            ]
        );
    }
}
