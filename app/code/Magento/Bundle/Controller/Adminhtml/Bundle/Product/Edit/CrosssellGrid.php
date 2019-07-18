<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit;

use Magento\Catalog\Controller\Adminhtml\Product\CrosssellGrid as CatalogCrosssellGrid;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class CrosssellGrid
 *
 * @package Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit
 * @deprecated Not used since cross-sell products grid moved to UI components.
 * @see Magento_Catalog::view/adminhtml/ui_component/crosssell_product_listing.xml
 */
class CrosssellGrid extends CatalogCrosssellGrid implements HttpPostActionInterface
{
}
