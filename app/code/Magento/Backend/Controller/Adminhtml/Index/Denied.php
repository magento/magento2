<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Controller\Adminhtml\Index;

use Magento\Backend\Controller\Adminhtml\Denied as DeniedController;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * To display Denied Page
 *
 * Class Denied
 */
class Denied extends DeniedController implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Backend::admin';
}
