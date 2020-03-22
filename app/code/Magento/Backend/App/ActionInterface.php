<?php
declare(strict_types=1);

namespace Magento\Backend\App;

use Magento\Framework\App\ActionInterface as FrameworkActionInterface;

interface ActionInterface extends FrameworkActionInterface
{
    /**
     * Name of "is URLs checked" flag
     */
    const FLAG_IS_URLS_CHECKED = 'check_url_settings';

    /**
     * Session namespace to refer in other places
     */
    const SESSION_NAMESPACE = 'adminhtml';

    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Magento_Backend::admin';
}
