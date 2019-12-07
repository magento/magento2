<?php
/**
 * Get related products grid
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class RelatedGrid
 *
 * @package Magento\Catalog\Controller\Adminhtml\Product
 * @deprecated Not used since related products grid moved to UI components.
 * @see Magento_Catalog::view/adminhtml/ui_component/related_product_listing.xml
 */
class RelatedGrid extends Related implements HttpPostActionInterface
{
}
