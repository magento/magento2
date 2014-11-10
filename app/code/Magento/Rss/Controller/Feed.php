<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Rss\Controller;

/**
 * Class Feed
 */
class Feed extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface
     */
    protected $customerAccountService;

    /**
     * @var \Magento\Framework\HTTP\Authentication
     */
    protected $httpAuthentication;

    /**
     * @var \Magento\Framework\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Rss\Model\RssManager
     */
    protected $rssManager;

    /**
     * @var \Magento\Rss\Model\RssFactory
     */
    protected $rssFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Rss\Model\RssManager $rssManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Rss\Model\RssFactory $rssFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccountService
     * @param \Magento\Framework\HTTP\Authentication $httpAuthentication
     * @param \Magento\Framework\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Rss\Model\RssManager $rssManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Rss\Model\RssFactory $rssFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccountService,
        \Magento\Framework\HTTP\Authentication $httpAuthentication,
        \Magento\Framework\Logger $logger
    ) {
        $this->rssManager = $rssManager;
        $this->scopeConfig = $scopeConfig;
        $this->rssFactory = $rssFactory;
        $this->customerSession = $customerSession;
        $this->customerAccountService = $customerAccountService;
        $this->httpAuthentication = $httpAuthentication;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function auth()
    {
        if (!$this->customerSession->isLoggedIn()) {
            list($login, $password) = $this->httpAuthentication->getCredentials();
            try {
                $customer = $this->customerAccountService->authenticate($login, $password);
                $this->customerSession->setCustomerDataAsLoggedIn($customer);
                $this->customerSession->regenerateId();
            } catch (\Exception $e) {
                $this->logger->logException($e);
            }
        }

        if (!$this->customerSession->isLoggedIn()) {
            $this->httpAuthentication->setAuthenticationFailed('RSS Feeds');
            return false;
        }

        return true;
    }
}
