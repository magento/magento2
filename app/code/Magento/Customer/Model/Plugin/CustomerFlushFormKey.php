<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey as DataFormKey;
use \Magento\Framework\Event\Observer;
use Magento\PageCache\Observer\FlushFormKey;

class CustomerFlushFormKey
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var DataFormKey
     */
    private $dataFormKey;

    /**
     * Initialize dependencies.
     *
     * @param Session $session
     * @param DataFormKey $dataFormKey
     */
    public function __construct(Session $session, DataFormKey $dataFormKey)
    {
        $this->session = $session;
        $this->dataFormKey = $dataFormKey;
    }

    /**
     * @param FlushFormKey $subject
     * @param callable $proceed
     * @param Observer $observer
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(FlushFormKey $subject, callable $proceed, Observer $observer)
    {
        $currentFormKey = $this->dataFormKey->getFormKey();
        $proceed($observer);
        $beforeParams = $this->session->getBeforeRequestParams();
        if (isset($beforeParams['form_key']) && $beforeParams['form_key'] === $currentFormKey) {
            $beforeParams['form_key'] = $this->dataFormKey->getFormKey();
            $this->session->setBeforeRequestParams($beforeParams);
        }
    }
}
