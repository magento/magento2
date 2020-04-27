<?php
/**
 * Depersonalize customer session data
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Layout;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Visitor;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\PageCache\Model\DepersonalizeChecker;

/**
 * Depersonalize customer data.
 *
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class DepersonalizePlugin
{
    /**
     * @var DepersonalizeChecker
     */
    private $depersonalizeChecker;

    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var Visitor
     */
    private $visitor;

    /**
     * @var int
     */
    private $customerGroupId;

    /**
     * @var string
     */
    private $formKey;

    /**
     * @param DepersonalizeChecker $depersonalizeChecker
     * @param SessionManagerInterface $session
     * @param CustomerSession $customerSession
     * @param CustomerFactory $customerFactory
     * @param Visitor $visitor
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        SessionManagerInterface $session,
        CustomerSession $customerSession,
        CustomerFactory $customerFactory,
        Visitor $visitor
    ) {
        $this->depersonalizeChecker = $depersonalizeChecker;
        $this->session = $session;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
        $this->visitor = $visitor;
    }

    /**
     * Retrieve sensitive customer data.
     *
     * @param LayoutInterface $subject
     * @return void
     */
    public function beforeGenerateXml(LayoutInterface $subject)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->customerGroupId = $this->customerSession->getCustomerGroupId();
            $this->formKey = $this->session->getData(FormKey::FORM_KEY);
        }
    }

    /**
     * Change sensitive customer data if the depersonalization is needed.
     *
     * @param LayoutInterface $subject
     * @return void
     */
    public function afterGenerateElements(LayoutInterface $subject)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            $this->visitor->setSkipRequestLogging(true);
            $this->visitor->unsetData();
            $this->session->clearStorage();
            $this->customerSession->clearStorage();
            $this->session->setData(FormKey::FORM_KEY, $this->formKey);
            $this->customerSession->setCustomerGroupId($this->customerGroupId);
            $this->customerSession->setCustomer($this->customerFactory->create()->setGroupId($this->customerGroupId));
        }
    }
}
