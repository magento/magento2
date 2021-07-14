<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Template\Html\Minifier\Php;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\PrettyPrinter\Standard;

class PrettyPrinter extends Standard
{
    /**
     * @var string[]
     */
    private $delayedHeredocs = [];

    protected function resetState()
    {
        $this->delayedHeredocs = [];
        $this->indentLevel = 0;
        $this->nl = '';
        $this->origTokens = null;
    }

    protected function setIndentLevel(int $level)
    {
        // Ignore indentation.
    }

    protected function indent()
    {
        // Ignore indentation.
    }

    protected function outdent()
    {
        // Ignore indentation.
    }

    /**
     * @param string $heredoc
     * @return string
     */
    private function getHeredocPlaceholder(string $heredoc): string
    {
        $index = count($this->delayedHeredocs) + 1;

        $this->delayedHeredocs[$index] = $this->handleMagicTokens($heredoc);

        return '__MINIFIED_HEREDOC__' . $index;
    }

    protected function pScalar_String(Node\Scalar\String_ $node): string
    {
        $result = parent::pScalar_String($node);

        return $node->getAttribute('kind') !== Node\Scalar\String_::KIND_HEREDOC
            ? $result
            : $this->getHeredocPlaceholder($result);
    }

    protected function pScalar_Encapsed(Node\Scalar\Encapsed $node): string
    {
        $result = parent::pScalar_Encapsed($node);

        return $node->getAttribute('kind') !== Node\Scalar\String_::KIND_HEREDOC
            ? $result
            : $this->getHeredocPlaceholder($result);
    }

    protected function pCommaSeparated(array $nodes): string
    {
        return $this->pImplode($nodes, ',');
    }

    protected function pComments(array $comments): string
    {
        // Only preserve doc comments.
        foreach ($comments as $key => $comment) {
            if (!$comment instanceof Comment\Doc) {
                unset($comments[$key]);
            }
        }

        $formattedComments = [];

        foreach ($comments as $comment) {
            $formattedComments[] = str_replace("\n", '', $comment->getReformattedText());
        }

        // Add a space between doc comments to avoid occurrences of "//" that could later be misinterpreted.
        return implode(' ', $formattedComments) . ' ';
    }

    protected function pExpr_Array(Node\Expr\Array_ $node): string
    {
        $node->setAttribute('kind', Node\Expr\Array_::KIND_SHORT);

        return parent::pExpr_Array($node);
    }

    protected function pStmt_Echo(Node\Stmt\Echo_ $node): string
    {
        $output = $this->pCommaSeparated($node->exprs);

        return $node->getAttribute('isSingleEchoStatement')
            ? $output . ' '
            : 'echo ' . $output . ';';
    }

    protected function pStmt_InlineHTML(Node\Stmt\InlineHTML $node): string
    {
        $newline = $node->getAttribute('hasLeadingNewline', true) ? "\n" : '';

        return '?>'
            . $newline
            . $node->value
            . ($node->getAttribute('hasSingleEchoStatementNext') ? '<?= ' : '<?php ');
    }

    /**
     * @return string[]
     */
    public function getDelayedHeredocs(): array
    {
        return $this->delayedHeredocs;
    }
}
