<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Payment\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\AdminOrder\Create;

/**
 * Class PaymentConfigurationProcess
 *
 * Removes inactive payment methods and group from checkout configuration.
 */
class AdminSetPaymentMethod
{
    /**
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly RequestInterface $request
    ) {
    }

    /**
     * Checkout LayoutProcessor before process plugin.
     *
     * @param Create $subject
     * @param array $data
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeImportPostData(Create $subject, array $data)
    {
//        if (isset($this->request->getParam('payment')['method'])) {
//            $subject->setPaymentMethod('');
//        }
    }
}
