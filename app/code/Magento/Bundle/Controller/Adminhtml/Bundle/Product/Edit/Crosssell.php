<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit;

use Magento\Catalog\Controller\Adminhtml\Product\Crosssell as CatalogCrossel;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class Crosssell
 *
 * @package Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit
 * @deprecated Not used since cross-sell products grid moved to UI components.
 * @see Magento_Catalog::view/adminhtml/ui_component/crosssell_product_listing.xml
 */
class Crosssell extends CatalogCrossel implements HttpPostActionInterface
{
}
