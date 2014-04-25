<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Block;

use Magento\Framework\View\Element\Template;

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
    protected $_shortcuts = array();

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
        array $data = array()
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
            array(
                'container' => $this,
                'is_catalog_product' => $this->_isCatalogProduct,
                'or_position' => $this->_orPosition
            )
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
