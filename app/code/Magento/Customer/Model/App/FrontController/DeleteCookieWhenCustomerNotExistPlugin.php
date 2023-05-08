<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\App\FrontController;

use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Customer\Model\Session;

/**
 * Plugin for delete the cookie when the customer is not exist.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class DeleteCookieWhenCustomerNotExistPlugin
{
    /**
     * @var ResponseHttp
     */
    private $responseHttp;

    /**
     * @var Session
     */
    private $session;

    /**
     * Constructor
     *
     * @param ResponseHttp $responseHttp
     * @param Session $session
     */
    public function __construct(
        ResponseHttp $responseHttp,
        Session $session
    ) {
        $this->responseHttp = $responseHttp;
        $this->session = $session;
    }

    /**
     * Delete the cookie when the customer is not exist before dispatch the front controller.
     *
     * @return void
     */
    public function beforeDispatch(): void
    {
        if (!$this->session->getCustomerId()) {
            $this->responseHttp->sendVary();
        }
    }
}
