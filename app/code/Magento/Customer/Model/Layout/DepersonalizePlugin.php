<?php
/**
 * Depersonalize customer session data
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
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Model\Layout;

/**
 * Class DepersonalizePlugin
 */
class DepersonalizePlugin
{
    /**
     * @var \Magento\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Magento\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var \Magento\Event\Manager
     */
    protected $eventManager;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var int
     */
    protected $customerGroupId;

    /**
     * @var string
     */
    protected $formKey;

    /**
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\Session\SessionManagerInterface $session
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Event\Manager $eventManager
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\View\LayoutInterface $layout,
        \Magento\Session\SessionManagerInterface $session,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Event\Manager $eventManager,
        \Magento\App\RequestInterface $request,
        \Magento\Module\Manager $moduleManager
    ) {
        $this->layout = $layout;
        $this->session = $session;
        $this->customerSession = $customerSession;
        $this->customer = $customerFactory->create();
        $this->eventManager = $eventManager;
        $this->request = $request;
        $this->moduleManager = $moduleManager;

    }

    /**
     * After layout generate
     *
     * @param \Magento\Core\Model\Layout $subject
     * @param mixed $result
     * @return mixed
     */
    public function afterGenerateXml(\Magento\Core\Model\Layout $subject, $result)
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache')
            && !$this->request->isAjax()
            && $this->layout->isCacheable()
        ) {
            $this->customerGroupId = $this->customerSession->getCustomerGroupId();
            $this->formKey = $this->session->getData(\Magento\Data\Form\FormKey::FORM_KEY);
            $this->eventManager->dispatch('before_session_write_close');
            session_write_close();
            $this->session->clearStorage();
            $this->customerSession->clearStorage();
            $this->session->setData(\Magento\Data\Form\FormKey::FORM_KEY, $this->formKey);
            $this->customerSession->setCustomerGroupId($this->customerGroupId);
            $this->customer->setGroupId($this->customerGroupId);
            $this->customerSession->setCustomer($this->customer);
        }
        return $result;
    }
}
