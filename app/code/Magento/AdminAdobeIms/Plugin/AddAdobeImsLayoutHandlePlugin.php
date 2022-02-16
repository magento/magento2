<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Plugin;

use Magento\Framework\View\Result\Layout;
use Magento\AdminAdobeIms\Service\ImsConfig;

class AddAdobeImsLayoutHandlePlugin
{
    /** @var ImsConfig */
    private ImsConfig $imsConfig;

    /**
     * @param ImsConfig $imsConfig
     */
    public function __construct(
        ImsConfig $imsConfig
    ) {
        $this->imsConfig = $imsConfig;
    }

    /**
     * @param Layout $subject
     * @param Layout $result
     * @return Layout
     */
    public function afterAddDefaultHandle(Layout $subject, Layout $result): Layout
    {
        if ($subject->getDefaultLayoutHandle() !== 'adminhtml_auth_login') {
            return $result;
        }

        if ($this->imsConfig->enabled() === true) {
            $result->addHandle('adobe_ims_login');
        }
        return $result;
    }
}
