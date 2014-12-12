<?php
/**
 * Delete products from Google Content
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items;

class MassDelete extends Refresh
{
    /**
     * Name of the operation to execute
     *
     * @var string
     */
    protected $operation = 'deleteItems';
}
