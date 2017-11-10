<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Cookie\Model\App\Response;

use Magento\Framework\App\Response\Http;
use Magento\Framework\Session\SessionManagerInterface;

/**
 * HTTP response plugin for frontend.
 */
class HttpPlugin
{
    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    public function __construct(
        SessionManagerInterface $sessionManager
    ) {
        $this->sessionManager = $sessionManager;
    }

    /**
     * We need this method because else __construct would not be called.
     *
     * @param Http $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSendResponse(Http $subject)
    {
        // Blank. Creating object that implements SessionManagerInterface is enough.
    }
}
