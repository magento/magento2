<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;
use Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive\DirectiveInterface;

/**
 * Class Text
 */
class Text implements TextInterface
{
    /**
     * @var DirectiveInterface[]
     */
    protected $directivePool;

    /**
     * Constructor
     *
     * @param DirectiveInterface[] $directivePool
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
     */
    public function compile(\DOMText $node, DataObject $processedObject)
    {
        $result = '';
        foreach ($this->directivePool as $directive) {
            $result = preg_replace_callback(
                $directive->getPattern(),
                function ($match) use ($directive, $processedObject) {
                    return $directive->execute($match, $processedObject);
                },
                $node->textContent
            );
        }

        $newNode = $node->ownerDocument->createTextNode($result);
        $node->parentNode->replaceChild($newNode, $node);
    }
}
