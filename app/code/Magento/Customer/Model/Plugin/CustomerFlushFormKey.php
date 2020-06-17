<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
     * @param $result
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterExecute(FlushFormKey $subject, $result)
    {
        $currentFormKey = $this->dataFormKey->getFormKey();
        $beforeParams = $this->session->getBeforeRequestParams();
        if (isset($beforeParams['form_key']) && $beforeParams['form_key'] === $currentFormKey) {
            $beforeParams['form_key'] = $this->dataFormKey->getFormKey();
            $this->session->setBeforeRequestParams($beforeParams);
        }
    }
}
