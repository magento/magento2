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
 */
class ShortcutButtons extends Template
{
    /**#@+
     * Position of "OR" label against shortcut
     */
    const POSITION_BEFORE = 'before';

    const POSITION_AFTER = 'after';

    /**#@-*/

    /**#@-*/
    protected $_shortcuts = [];

    /**
     * @var bool
     */
    protected $_isCatalogProduct;

    /**
     * @var null|string
     */
    protected $_orPosition;

    /**
     * @param Template\Context $context
     * @param bool $isCatalogProduct
     * @param null|string $orPosition
     * @param array $data
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
