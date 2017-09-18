<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\DataObject;
use Magento\Framework\View\TemplateEngine\Xhtml\Compiler\Directive\DirectiveInterface;

/**
 * Class Attribute
 */
class Attribute implements AttributeInterface
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
     * @param \DOMAttr $node
     * @param DataObject $processedObject
     * @return void
     */
    public function compile(\DOMAttr $node, DataObject $processedObject)
    {
        foreach ($this->directivePool as $directive) {
            $node->value = preg_replace_callback(
                $directive->getPattern(),
                function ($match) use ($directive, $processedObject) {
                    return $directive->execute($match, $processedObject);
                },
                $node->value
            );
        }
    }
}
