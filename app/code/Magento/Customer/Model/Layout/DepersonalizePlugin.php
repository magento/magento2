<?php
/**
 * Depersonalize customer session data
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Layout;

use Magento\PageCache\Model\DepersonalizeChecker;

/**
 * Class DepersonalizePlugin
 * @since 2.0.0
 */
class DepersonalizePlugin
{
    /**
     * @var DepersonalizeChecker
     * @since 2.0.0
     */
    protected $depersonalizeChecker;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     * @since 2.0.0
     */
    protected $session;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     * @since 2.0.0
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\Visitor
     * @since 2.0.0
     */
    protected $visitor;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $customerGroupId;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $formKey;

    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Visitor $visitor
     * @since 2.0.0
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Visitor $visitor
    ) {
        $this->session = $session;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->visitor = $visitor;
        $this->depersonalizeChecker = $depersonalizeChecker;
    }

    /**
     * Before generate Xml
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @return array
     * @since 2.0.0
     */
    public function beforeGenerateXml(\Magento\Framework\View\LayoutInterface $subject)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
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
     * @since 2.0.0
     */
    public function afterGenerateXml(\Magento\Framework\View\LayoutInterface $subject, $result)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
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
