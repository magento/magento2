<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block;

use Magento\Framework\View\Element\Template;

/**
 * Shortcuts container
 *
 * Accepts shortcuts on shortcut_buttons_container event and render shortcuts using custom order
 *
 * @api
 * @since 2.0.0
 */
class ShortcutButtons extends Template
{
    /**#@+
     * Position of "OR" label against shortcut
     */
    const POSITION_BEFORE = 'before';

    const POSITION_AFTER = 'after';

    /**#@-*/

    /**
     * @var array
     */
    protected $_shortcuts = [];

    /**
     * @var bool
     * @since 2.0.0
     */
    protected $_isCatalogProduct;

    /**
     * @var null|string
     * @since 2.0.0
     */
    protected $_orPosition;

    /**
     * @param Template\Context $context
     * @param bool $isCatalogProduct
     * @param null|string $orPosition
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Template\Context $context,
        $isCatalogProduct = false,
        $orPosition = null,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isCatalogProduct = $isCatalogProduct;
        $this->_orPosition = $orPosition ?: ($isCatalogProduct ? self::POSITION_BEFORE : self::POSITION_AFTER);
    }

    /**
     * Add shortcut button
     *
     * @param Template $block
     * @return void
     * @since 2.0.0
     */
    public function addShortcut(Template $block)
    {
        if ($block instanceof ShortcutInterface) {
            $this->_shortcuts[] = $block;
        }
    }

    /**
     * Dispatch shortcuts container event
     * @return $this
     * @since 2.0.0
     */
    protected function _beforeToHtml()
    {
        $this->_eventManager->dispatch(
            'shortcut_buttons_container',
            [
                'container' => $this,
                'is_catalog_product' => $this->_isCatalogProduct,
                'or_position' => $this->_orPosition
            ]
        );
        return $this;
    }

    /**
     * Render all shortcuts
     *
     * @return string
     * @since 2.0.0
     */
    protected function _toHtml()
    {
        /** @var ShortcutInterface $shortcut */
        foreach ($this->_shortcuts as $shortcut) {
            $this->setChild($shortcut->getAlias(), $shortcut);
        }
        return $this->getChildHtml();
    }
}
