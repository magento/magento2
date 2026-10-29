<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit;

use Magento\Catalog\Controller\Adminhtml\Product\Related as CatalogRelated;
use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Class Related
 *
 * @package Magento\Bundle\Controller\Adminhtml\Bundle\Product\Edit
 * @deprecated Not used since related products grid moved to UI components.
 * @see Magento_Catalog::view/adminhtml/ui_component/related_product_listing.xml
 */
class Related extends CatalogRelated implements HttpPostActionInterface
{
}
