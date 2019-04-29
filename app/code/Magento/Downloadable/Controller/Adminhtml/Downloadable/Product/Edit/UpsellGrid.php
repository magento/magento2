<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit;

use Magento\Catalog\Controller\Adminhtml\Product\UpsellGrid as CatalogUpsellGrid;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class UpsellGrid
 *
 * @package Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit
 * @deprecated Not used since upsell products grid moved to UI components.
 * @see Magento_Catalog::view/adminhtml/ui_component/upsell_product_listing.xml
 */
class UpsellGrid extends CatalogUpsellGrid implements HttpPostActionInterface
{
}
