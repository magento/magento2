<?php
/**
 *
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
namespace Magento\Customer\Controller\Account;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;
use Magento\Framework\App\Action\Action;

class Create extends \Magento\Customer\Controller\Account
{
    /** @var \Magento\Customer\Helper\Data */
    protected $customerHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param CustomerAccountServiceInterface $customerAccountService
     * @param \Magento\Customer\Helper\Data $customerHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CustomerAccountServiceInterface $customerAccountService,
        \Magento\Customer\Helper\Data $customerHelper
    ) {
        $this->customerHelper = $customerHelper;
        parent::__construct(
            $context,
            $customerSession,
            $addressHelper,
            $urlFactory,
            $storeManager,
            $scopeConfig,
            $customerAccountService
        );
    }

    /**
     * Customer register form page
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_getSession()->isLoggedIn() || !$this->customerHelper->isRegistrationAllowed()) {
            $this->_redirect('*/*');
            return;
        }

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}
