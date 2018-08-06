<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\ProductList\Item;

use Magento\Catalog\Block\Product\AwareInterface as ProductAwareInterface;

/**
 * Class List Item Block Container
 *
 * @api
 * @since 101.0.1
 */
class Container extends Block
{
    /**
     * {@inheritdoc}
     * @since 101.0.1
     */
    public function getChildHtml($alias = '', $useCache = false)
    {
        $layout = $this->getLayout();
        if ($layout) {
            $name = $this->getNameInLayout();
            foreach ($layout->getChildBlocks($name) as $child) {
                if ($child instanceof ProductAwareInterface) {
                    $child->setProduct($this->getProduct());
                }
            }
        }
        return parent::getChildHtml($alias, $useCache);
    }
}
