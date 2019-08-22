<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit;

use Magento\Catalog\Controller\Adminhtml\Product\Upsell as CatalogUpsell;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class Upsell
 *
 * @package Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit
 * @deprecated Not used since upsell products grid moved to UI components.
 * @see Magento_Catalog::view/adminhtml/ui_component/upsell_product_listing.xml
 */
class Upsell extends CatalogUpsell implements HttpPostActionInterface
{
}
