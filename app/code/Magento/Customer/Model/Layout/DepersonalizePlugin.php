<?php
/**
 * Depersonalize customer session data
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Layout;

/**
 * Class DepersonalizePlugin
 */
class DepersonalizePlugin
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $session;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Customer\Model\Visitor
     */
    protected $visitor;

    /**
     * @var int
     */
    protected $customerGroupId;

    /**
     * @var string
     */
    protected $formKey;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $cacheConfig;

    /**
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Customer\Model\Visitor $visitor
     * @param \Magento\PageCache\Model\Config $cacheConfig
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Customer\Model\Visitor $visitor,
        \Magento\PageCache\Model\Config $cacheConfig
    ) {
        $this->session = $session;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->request = $request;
        $this->moduleManager = $moduleManager;
        $this->visitor = $visitor;
        $this->cacheConfig = $cacheConfig;
    }

    /**
     * Before generate Xml
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @return array
     */
    public function beforeGenerateXml(\Magento\Framework\View\LayoutInterface $subject)
    {
        if ($this->moduleManager->isEnabled(
            'Magento_PageCache'
        ) && $this->cacheConfig->isEnabled() && !$this->request->isAjax() && $subject->isCacheable()
        ) {
            $this->customerGroupId = $this->customerSession->getCustomerGroupId();
            $this->formKey = $this->session->getData(\Magento\Framework\Data\Form\FormKey::FORM_KEY);
        }
        return [];
    }

    /**
     * After generate Xml
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @param \Magento\Framework\View\LayoutInterface $result
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function afterGenerateXml(\Magento\Framework\View\LayoutInterface $subject, $result)
    {
        if ($this->moduleManager->isEnabled('Magento_PageCache')
            && $this->cacheConfig->isEnabled()
            && !$this->request->isAjax()
            && $subject->isCacheable()
        ) {
            $this->visitor->setSkipRequestLogging(true);
            $this->visitor->unsetData();
            $this->session->clearStorage();
            $this->customerSession->clearStorage();
            $this->session->setData(\Magento\Framework\Data\Form\FormKey::FORM_KEY, $this->formKey);
            $this->customerSession->setCustomerGroupId($this->customerGroupId);
            $this->customerSession->setCustomer($this->customerFactory->create()->setGroupId($this->customerGroupId));
        }
        return $result;
    }
}
