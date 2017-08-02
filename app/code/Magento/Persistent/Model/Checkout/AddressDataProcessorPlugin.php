<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model\Checkout;

use Magento\Quote\Api\Data\AddressAdditionalDataInterface as AddressAdditionalData;
use Magento\Persistent\Helper\Session as PersistentSession;
use Magento\Persistent\Helper\Data as PersistentHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Model\AddressAdditionalDataProcessor;

/**
 * Class \Magento\Persistent\Model\Checkout\AddressDataProcessorPlugin
 *
 * @since 2.0.0
 */
class AddressDataProcessorPlugin
{
    /**
     * @var PersistentSession
     * @since 2.0.0
     */
    private $persistentSession;

    /**
     * @var PersistentHelper
     * @since 2.0.0
     */
    private $persistentHelper;

    /**
     * @var CheckoutSession
     * @since 2.0.0
     */
    private $checkoutSession;

    /**
     * @param PersistentHelper $persistentHelper
     * @param PersistentSession $persistentSession
     * @param CheckoutSession $checkoutSession
     * @since 2.0.0
     */
    public function __construct(
        PersistentHelper $persistentHelper,
        PersistentSession $persistentSession,
        CheckoutSession $checkoutSession
    ) {
        $this->persistentHelper = $persistentHelper;
        $this->persistentSession = $persistentSession;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Process address additional data
     *
     * @param AddressAdditionalDataProcessor $subject
     * @param AddressAdditionalData $additionalData
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function beforeProcess(AddressAdditionalDataProcessor $subject, AddressAdditionalData $additionalData)
    {
        if (!$this->persistentHelper->isEnabled() || !$this->persistentHelper->isRememberMeEnabled()) {
            return;
        }
        $checkboxStatus = $additionalData->getExtensionAttributes()->getPersistentRememberMe();
        $isRememberMeChecked = empty($checkboxStatus) ? false : true;
        $this->persistentSession->setRememberMeChecked($isRememberMeChecked);
        $this->checkoutSession->setRememberMeChecked($isRememberMeChecked);
    }
}
