<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;
use Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive\DirectiveInterface;

/**
 * Class Text
 * @since 2.0.0
 */
class Text implements TextInterface
{
    /**
     * @var DirectiveInterface[]
     * @since 2.0.0
     */
    protected $directivePool;

    /**
     * Constructor
     *
     * @param DirectiveInterface[] $directivePool
     * @since 2.0.0
     */
    public function __construct(array $directivePool)
    {
        $this->directivePool = $directivePool;
    }

    /**
     * Compiles the Element node
     *
     * @param \DOMText $node
     * @param DataObject $processedObject
     * @return void
     * @since 2.0.0
     */
    public function compile(\DOMText $node, DataObject $processedObject)
    {
        $result = $node->textContent;
        foreach ($this->directivePool as $directive) {
            $result = preg_replace_callback(
                $directive->getPattern(),
                function ($match) use ($directive, $processedObject) {
                    return $directive->execute($match, $processedObject);
                },
                $result
            );
        }

        $newNode = $node->ownerDocument->createTextNode($result);
        $node->parentNode->replaceChild($newNode, $node);
    }
}
