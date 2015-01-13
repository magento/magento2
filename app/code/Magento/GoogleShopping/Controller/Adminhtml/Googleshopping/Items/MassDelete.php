<?php
/**
 * Delete products from Google Content
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
