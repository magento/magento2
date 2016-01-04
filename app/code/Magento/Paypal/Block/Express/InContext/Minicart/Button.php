<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Block\Express\InContext\Minicart;

use Magento\Paypal\Block\Express\InContext;
use Magento\Catalog\Block\ShortcutInterface;

/**
 * Class Button
 */
class Button extends InContext\Button implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    /**
     * @var bool
     */
    private $isMiniCart = false;

    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @param bool $isCatalog
     * @return $this
     */
    public function setIsInCatalogProduct($isCatalog)
    {
        $this->isMiniCart = !$isCatalog;

        return $this;
    }

    /**
     * @return bool
     */
    protected function shouldRender()
    {
        return parent::shouldRender() && $this->isMiniCart;
    }
}
