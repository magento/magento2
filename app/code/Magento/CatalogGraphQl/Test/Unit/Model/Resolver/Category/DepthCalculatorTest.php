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
        /** @var SelectionSetNode $selectionSetMock */
        $selectionSetMock = $this->createMock(SelectionSetNode::class);
        $selectionSetMock->selections = $this->getMockSelectionsArrayForNullCase();
        $this->fieldNodeMock->selectionSet = $selectionSetMock;
        $result = $this->depthCalculator->calculate($this->resolveInfoMock, $this->fieldNodeMock);
        $this->assertEquals(1, $result);
    }

    /**
     * Await for depth of '2' if selectionSet is not null
     * @return void
     */
    public function testCalculateNonNullAsSelectionSet(): void
    {
        $this->fieldNodeMock->kind = NodeKind::FIELD;
        $selectionSetMock = $this->createMock(SelectionSetNode::class);
        $selectionSetMock->selections = $this->getMockSelectionsArrayForNonNullCase();
        $this->fieldNodeMock->selectionSet = $selectionSetMock;
        $result = $this->depthCalculator->calculate($this->resolveInfoMock, $this->fieldNodeMock);
        $this->assertEquals(2, $result);
    }

    /**
     * @return NodeList
     */
    private function getMockSelectionsArrayForNullCase()
    {
        /** @var SelectionSetNode $selectionSetMock */
        $selectionSetMock = $this->createMock(SelectionSetNode::class);
        $selectionSetMock->selections = [$this->getNewFieldNodeMock()];
        $inlineFragmentMock = $this->getNewInlineFragmentNodeMock();
        $inlineFragmentMock->selectionSet = $selectionSetMock;
        return new NodeList([
            $this->getNewFieldNodeMock(),
            $inlineFragmentMock
        ]);
    }

    /**
     * @return FieldNode|MockObject
     */
    private function getNewFieldNodeMock()
    {
        return $this->getMockBuilder(FieldNode::class)
            ->setConstructorArgs(['vars' => []])
            ->getMock();
    }

    /**
     * @return InlineFragmentNode|MockObject
     */
    private function getNewInlineFragmentNodeMock()
    {
        return $this->getMockBuilder(InlineFragmentNode::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return NodeList
     */
    private function getMockSelectionsArrayForNonNullCase()
    {
        $newFieldMock = $this->getNewFieldNodeMock();
        $newFieldMock->selectionSet = $this->createMock(SelectionSetNode::class);
        $newFieldMock->selectionSet->selections = [$this->getNewFieldNodeMock()];
        /** @var SelectionSetNode $selectionSetMock */
        $selectionSetMock = $this->createMock(SelectionSetNode::class);
        $selectionSetMock->selections = [$newFieldMock];

        $inlineFragmentMock = $this->getNewInlineFragmentNodeMock();
        $inlineFragmentMock->selectionSet = $selectionSetMock;
        return new NodeList([
            $this->getNewFieldNodeMock(),
            $inlineFragmentMock
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
}
