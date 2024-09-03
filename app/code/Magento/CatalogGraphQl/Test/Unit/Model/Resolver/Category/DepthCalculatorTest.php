<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Test\Unit\Model\Resolver\Category;

use GraphQL\Language\AST\FieldNode;
use GraphQL\Language\AST\InlineFragmentNode;
use GraphQL\Language\AST\NodeKind;
use GraphQL\Language\AST\NodeList;
use GraphQL\Language\AST\SelectionSetNode;
use Magento\CatalogGraphQl\Model\Category\DepthCalculator;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DepthCalculatorTest extends TestCase
{
    /**
     * @var DepthCalculator
     */
    private DepthCalculator $depthCalculator;

    /**
     * @var ResolveInfo|MockObject
     */
    private $resolveInfoMock;

    /**
     * @var FieldNode|MockObject
     */
    private $fieldNodeMock;

    /**
     * Await for depth of '1' if selectionSet is null
     * @return void
     */
    public function testCalculateWithNullAsSelectionSet(): void
    {
        $this->fieldNodeMock->kind = NodeKind::FIELD;
        /** @var SelectionSetNode $selectionSetNode */
        $selectionSetNode = new SelectionSetNode([]);
        $selectionSetNode->selections = $this->getSelectionsArrayForNullCase();
        $this->fieldNodeMock->selectionSet = $selectionSetNode;
        $result = $this->depthCalculator->calculate($this->resolveInfoMock, $this->fieldNodeMock);
        $this->assertSame(1, $result);
    }

    /**
     * Await for depth of '2' if selectionSet is not null
     * @return void
     */
    public function testCalculateNonNullAsSelectionSet(): void
    {
        $this->fieldNodeMock->kind = NodeKind::FIELD;
        $selectionSetNode = $this->getSelectionSetNode();
        $selectionSetNode->selections = $this->getSelectionsArrayForNonNullCase();
        $this->fieldNodeMock->selectionSet = $selectionSetNode;
        $result = $this->depthCalculator->calculate($this->resolveInfoMock, $this->fieldNodeMock);
        $this->assertEquals(2, $result);
    }

    /**
     * @return NodeList
     */
    private function getSelectionsArrayForNullCase()
    {
        $selectionSetNode = $this->getSelectionSetNode();
        $selectionSetNode->selections = $this->getNodeList();
        $inlineFragmentNode = $this->getNewInlineFragmentNode();
        $inlineFragmentNode->selectionSet = $selectionSetNode;
        return new NodeList([
            $this->getNewFieldNode(),
            $inlineFragmentNode
        ]);
    }

    /**
     * @return FieldNode
     */
    private function getNewFieldNode()
    {
        return new FieldNode([]);
    }

    /**
     * @return InlineFragmentNode
     */
    private function getNewInlineFragmentNode()
    {
        return new InlineFragmentNode([]);
    }

    /**
     * @return NodeList
     */
    private function getSelectionsArrayForNonNullCase()
    {
        $newFieldNode = $this->getNewFieldNode();
        $newFieldNode->selectionSet = $this->getSelectionSetNode();
        $newFieldNode->selectionSet->selections = $this->getNodeList();
        $newFieldNode->selectionSet->selections[] = $this->getNewFieldNode();
        $selectionSetNode = $this->getSelectionSetNode();
        $selectionSetNode->selections = new NodeList([$newFieldNode]);
        $inlineFragmentNode = $this->getNewInlineFragmentNode();
        $inlineFragmentNode->selectionSet = $selectionSetNode;
        return new NodeList([
            $newFieldNode,
            $inlineFragmentNode
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->depthCalculator = new DepthCalculator();
        $this->resolveInfoMock = $this->createMock(ResolveInfo::class);
        $this->fieldNodeMock = $this->getMockBuilder(FieldNode::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \GraphQL\Language\AST\SelectionSetNode
     */
    protected function getSelectionSetNode($nodes = []): SelectionSetNode
    {
        return new SelectionSetNode($nodes);
    }

    /**
     * @return \GraphQL\Language\AST\NodeList
     */
    protected function getNodeList(): NodeList
    {
        return new NodeList([]);
    }
}
