<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Controller\Adminhtml\Subscription;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Analytics\Model\AnalyticsToken;
use Magento\Config\Model\Config;
use Magento\Framework\HTTP\ZendClientFactory as HttpClientFactory;
use Magento\Framework\HTTP\ZendClient as HttpClient;
use Zend_Http_Response as HttpResponse;

class Redirect extends Action
{
    private $analyticsToken;

    /**
     * @param AnalyticsToken $analyticsToken
     */
    public function __construct(
        AnalyticsToken $analyticsToken
    ) {
        $this->analyticsToken = $analyticsToken;
    }

    /**
     * Redirect to external reports service based on subscription status
     *
     * @return $resultRedirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        if ($this->analyticsToken->isTokenExist()) {
            // code
        } else {
            // code
        }

        return $resultRedirect;
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Analytics::analytics_settings');
    }
}
