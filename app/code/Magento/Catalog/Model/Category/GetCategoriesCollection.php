<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Category;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\TreeFactory;
use Magento\Framework\Data\Tree\Node\Collection as NodeCollection;

/**
 * Returns categories collection
 */
class GetCategoriesCollection
{
    /**
     * @var TreeFactory
     */
    private $categoryTreeFactory;

    /**
     * @param TreeFactory $categoryTreeFactory
     */
    public function __construct(TreeFactory $categoryTreeFactory)
    {
        $this->categoryTreeFactory = $categoryTreeFactory;
    }

    /**
     * Returns collection
     *
     * @param int $parent
     * @param int|null $recursionLevel
     * @param bool|null $sorted
     * @param bool|null $asCollection
     * @param bool|null $toLoad
     * @param bool|null $onlyActive
     * @param bool|null $onlyIncludeInMenu
     * @return NodeCollection|Collection
     */
    public function execute(
        int $parent,
        ?int $recursionLevel = 0,
        ?bool $sorted = false,
        ?bool $asCollection = false,
        ?bool $toLoad = true,
        ?bool $onlyActive = true,
        ?bool $onlyIncludeInMenu = true
    ) {
        $tree = $this->categoryTreeFactory->create();
        $nodes = $tree->loadNode($parent)
            ->loadChildren($recursionLevel)
            ->getChildren();

        $tree->addCollectionDataParams(null, $sorted, $parent, $toLoad, $onlyActive, $onlyIncludeInMenu);

        return $asCollection ? $tree->getCollection() : $nodes;
    }
}
