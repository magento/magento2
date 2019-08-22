<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit;

use Magento\Catalog\Controller\Adminhtml\Product\Crosssell as CatalogCrosssell;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class Crosssell
 *
 * @package Magento\Downloadable\Controller\Adminhtml\Downloadable\Product\Edit
 * @deprecated Not used since cross-sell products grid moved to UI components.
 * @see Magento_Catalog::view/adminhtml/ui_component/crosssell_product_listing.xml
 */
class Crosssell extends CatalogCrosssell implements HttpPostActionInterface
{
}
