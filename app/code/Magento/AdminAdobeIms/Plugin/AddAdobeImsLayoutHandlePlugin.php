<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\Framework\View\Result\Layout;
use Magento\AdminAdobeIms\Service\ImsConfig;

/**
 * Plugin to add Adobe ims layout handle when module is active
 */
class AddAdobeImsLayoutHandlePlugin
{
    /** @var ImsConfig */
    private ImsConfig $adminImsConfig;

    /**
     * @param ImsConfig $adminImsConfig
     */
    public function __construct(
        ImsConfig $adminImsConfig
    ) {
        $this->adminImsConfig = $adminImsConfig;
    }

    /**
     * Add our admin hand only when on the login page and module is active
     *
     * @param Layout $subject
     * @param Layout $result
     * @return Layout
     */
    public function afterAddDefaultHandle(Layout $subject, Layout $result): Layout
    {
        if ($subject->getDefaultLayoutHandle() !== 'adminhtml_auth_login') {
            return $result;
        }

        if ($this->adminImsConfig->enabled() !== true) {
            return $result;
        }

        $result->addHandle('adobe_ims_login');
        return $result;
    }
}
