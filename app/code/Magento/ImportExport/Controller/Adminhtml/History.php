<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * History controller
 */
abstract class History extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Magento_ImportExport::history';
}
