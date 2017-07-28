<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Description\Mixin;

/**
 * Create mixin instance based on type
 * @since 2.2.0
 */
class MixinFactory
{
    /**#@+
     * Constants for existing mixin types
     */
    const SPAN_MIXIN = 'span';
    const BOLD_MIXIN = 'b';
    const BRAKE_MIXIN = 'br';
    const PARAGRAPH_MIXIN = 'p';
    const HEADER_MIXIN = 'h1';
    const ITALIC_MIXIN = 'i';
    /**#@-*/

    /**
     * @var array
     */
    private $typeClassMap = [
        self::SPAN_MIXIN => SpanMixin::class,
        self::BOLD_MIXIN => BoldMixin::class,
        self::BRAKE_MIXIN => BrakeMixin::class,
        self::PARAGRAPH_MIXIN => ParagraphMixin::class,
        self::HEADER_MIXIN => HeaderMixin::class,
        self::ITALIC_MIXIN => ItalicMixin::class,
    ];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @throws \Magento\Setup\Exception
     * @since 2.2.0
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create mixin by type
     *
     * @param string $mixinType
     * @return \Magento\Setup\Model\Description\Mixin\DescriptionMixinInterface
     * @throws \InvalidArgumentException
     * @since 2.2.0
     */
    public function create($mixinType)
    {
        if (!isset($this->typeClassMap[$mixinType])) {
            throw new \InvalidArgumentException(sprintf('Undefined mixin type: %s.', $mixinType));
        }

        $mixin = $this->objectManager->get($this->typeClassMap[$mixinType]);

        if (!$mixin instanceof \Magento\Setup\Model\Description\Mixin\DescriptionMixinInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Class "%s" must implement \Magento\Setup\Model\Description\Mixin\DescriptionMixinInterface.',
                    get_class($mixin)
                )
            );
        }

        return $mixin;
    }
}
