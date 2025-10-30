<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

namespace Magento\Customer\Model\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey as DataFormKey;
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param FlushFormKey $subject
     * @param callable $proceed
     * @param $args
     */
    public function aroundExecute(FlushFormKey $subject, callable $proceed, ...$args)
    {
        $currentFormKey = $this->dataFormKey->getFormKey();
        $proceed(...$args);
        $beforeParams = $this->session->getBeforeRequestParams();
        if (isset($beforeParams['form_key']) && $beforeParams['form_key'] === $currentFormKey) {
            $beforeParams['form_key'] = $this->dataFormKey->getFormKey();
            $this->session->setBeforeRequestParams($beforeParams);
        }
    }
}
