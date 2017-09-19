<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Controller\Adminhtml\Downloadable;

/**
 * Downloadable File upload controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class File extends \Magento\Backend\App\Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';
}
