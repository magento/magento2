<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Controller\Adminhtml\Foo;


use Magento\AuthorizenetAcceptjs\Model\AuthorizenetGateway\ApiClient;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;

class Bar extends  Action
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    public function __construct(
        ApiClient $apiClient,
        Action\Context $context)
    {
        parent::__construct($context);
        $this->apiClient = $apiClient;
    }

    public function execute()
    {
        $request = $this->apiClient->createAuthenticatedRequest();
        $this->apiClient->sendRequest($request);
    }
}