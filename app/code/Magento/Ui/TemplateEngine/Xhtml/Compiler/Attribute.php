<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\TemplateEngine\Xhtml\Compiler;

use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\TemplateEngine\Xhtml\Compiler\Directive\DirectiveInterface;

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
     * @param UiComponentInterface $component
     * @return void
     */
    public function compile(\DOMAttr $node, UiComponentInterface $component)
    {
        foreach ($this->directivePool as $directive) {
            $node->value = preg_replace_callback(
                $directive->getPattern(),
                function ($match) use ($directive, $component) {
                    return $directive->execute($match, $component);
                },
                $node->value
            );
        }
    }
}
