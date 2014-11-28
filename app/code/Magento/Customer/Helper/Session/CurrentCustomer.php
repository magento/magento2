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
namespace Magento\Customer\Helper\Session;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\LayoutInterface;
use Magento\Customer\Api\Data\CustomerDataBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\App\ViewInterface;

/**
 * Class CurrentCustomer
 */
class CurrentCustomer
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Customer\Api\Data\CustomerDataBuilder
     */
    protected $customerBuilder;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @param CustomerSession $customerSession
     * @param LayoutInterface $layout
     * @param CustomerDataBuilder $customerBuilder
     * @param CustomerRepositoryInterface $customerRepository
     * @param RequestInterface $request
     * @param ModuleManager $moduleManager
     * @param ViewInterface $view
     */
    public function __construct(
        CustomerSession $customerSession,
        LayoutInterface $layout,
        CustomerDataBuilder $customerBuilder,
        CustomerRepositoryInterface $customerRepository,
        RequestInterface $request,
        ModuleManager $moduleManager,
        ViewInterface $view
    ) {
        $this->customerSession = $customerSession;
        $this->layout = $layout;
        $this->customerBuilder = $customerBuilder;
        $this->customerRepository = $customerRepository;
        $this->request = $request;
        $this->moduleManager = $moduleManager;
        $this->view = $view;
    }

    /**
     * Returns customer Data with customer group only
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function getDepersonalizedCustomer()
    {
        return $this->customerBuilder->setGroupId($this->customerSession->getCustomerGroupId())->create();
    }

    /**
     * Returns customer Data from service
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function getCustomerFromService()
    {
        return $this->customerRepository->getById($this->customerSession->getId());
    }

    /**
     * Returns current customer according to session and context
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomer()
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache')
            && !$this->request->isAjax()
            && $this->view->isLayoutLoaded()
            && $this->layout->isCacheable()
        ) {
            return $this->getDepersonalizedCustomer();
        } else {
            return $this->getCustomerFromService();
        }
    }

    /**
     * Returns customer id from session
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->customerSession->getId();
    }

    /**
     * Set customer id
     *
     * @param int|null $customerId
     * @return void
     */
    public function setCustomerId($customerId)
    {
        $this->customerSession->setId($customerId);
    }
}
