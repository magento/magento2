<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\Menu;

use Magento\Framework\Data\Tree\Node;
use Magento\Theme\Api\GetMenuInterface;
use Magento\Theme\Api\Data\MenuNodeInterface;

/**
 * Adapter to fetch data from new GetMenuInterface service and pass it to legacy tree node.
 */
class TopMenuAdapter
{
    /**
     * @var GetMenuInterface
     */
    private $getMenu;

    /**
     * @var TopMenuNodeDataBuilderInterface[]
     */
    private $topMenuNodeDataBuilders;

    /**
     * TopMenuAdapter constructor.
     * @param GetMenuInterface $getMenu
     * @param TopMenuNodeDataBuilderInterface $standardTopMenuDataBuilder
     * @param TopMenuNodeDataBuilderInterface[] $topMenuNodeDataBuilders
     */
    public function __construct(
        GetMenuInterface $getMenu,
        TopMenuNodeDataBuilderInterface $standardTopMenuDataBuilder,
        array $topMenuNodeDataBuilders = []
    ) {
        $this->getMenu = $getMenu;
        $this->topMenuNodeDataBuilders = array_map(
            function (TopMenuNodeDataBuilderInterface $dataBuilder) {
                return $dataBuilder;
            },
            $topMenuNodeDataBuilders
        );
        $this->topMenuNodeDataBuilders[] = $standardTopMenuDataBuilder;
    }

    /**
     * Export menu nodes from menu to legacy Tree Nodes of Top Menu
     *
     * @param Node $node
     * @return Node
     */
    public function exportToMenuTreeNode(Node $node): Node
    {
        foreach ($this->getMenu->execute() as $menuNode) {
            $node->addChild($this->convertMenuNode($menuNode, $node));
        }


        return $node;
    }

    private function convertMenuNode(MenuNodeInterface $menuNode, Node $parentTreeNode): Node
    {
        $data = $this->buildTreeNodeData($menuNode);
        $treeNode = new Node(
            $data,
            'id',
            $parentTreeNode->getTree(),
            $parentTreeNode
        );

        foreach ($menuNode->getChildren() as $childNode) {
            $treeNode->addChild($this->convertMenuNode($childNode, $treeNode));
        }

        return $treeNode;
    }

    private function buildTreeNodeData(MenuNodeInterface $menuNode)
    {
        foreach ($this->topMenuNodeDataBuilders as $topMenuNodeDataBuilder) {
            if ($topMenuNodeDataBuilder->isAcceptable($menuNode)) {
                return $topMenuNodeDataBuilder->buildData($menuNode);
            }
        }
    }
}
