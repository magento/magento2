<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Template\Html\Minifier\Php;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;

class NodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var Node[]
     */
    private $stack = [];

    /**
     * @var ?Node
     */
    private $previous;

    /**
     * @var int
     */
    private $heredocCount = 0;

    public function beforeTraverse(array $nodes)
    {
        $this->stack = [];
        $this->previous = null;
    }

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Stmt) {
            // Mark isolated echo statements, to replace them later with short echo tags.
            $parent = empty($this->stack) ?: $this->stack[count($this->stack) - 1];

            if ($node instanceof Node\Stmt\InlineHTML) {
                $node->setAttribute('parent', $parent);

                if (
                    ($this->previous instanceof Node\Stmt\Echo_)
                    && ($previousHtmlStatement = $this->previous->getAttribute('previousHtmlStatement'))
                    && ($previousHtmlStatement->getAttribute('parent') === $parent)
                ) {
                    $this->previous->setAttribute('isSingleEchoStatement', true);
                    $previousHtmlStatement->setAttribute('hasSingleEchoStatementNext', true);
                }
            } elseif (
                ($this->previous instanceof Node\Stmt\InlineHTML)
                && ($this->previous->getAttribute('parent') === $parent)
            ) {
                $node->setAttribute('previousHtmlStatement', $this->previous);
            }
        }

        $this->stack[] = $node;
    }

    public function leaveNode(Node $node)
    {
        $this->previous = $node;

        array_pop($this->stack);

        // Remove nodes that only contain non-doc comments.
        if ($node instanceof Node\Stmt\Nop) {
            $comments = $node->getComments();
            $isSuperfluousNode = true;

            foreach ($comments as $key => $comment) {
                if ($comment instanceof Comment\Doc) {
                    $isSuperfluousNode = false;
                    break;
                }
            }

            if ($isSuperfluousNode) {
                return NodeTraverser::REMOVE_NODE;
            }
        }
    }
}
