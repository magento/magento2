<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Block\Checkout;

class LayoutProcessor implements \Magento\Checkout\Block\Checkout\LayoutProcessorInterface
{
    /**
     * @var \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface
     */
    protected $checkoutAgreementsRepository;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @param \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->checkoutAgreementsRepository = $checkoutAgreementsRepository;
        $this->escaper = $escaper;
    }

    /**
     * {@inheritdoc}
     */
    public function process($jsLayout)
    {
        $agreementConfiguration = [];
        $agreementsList = $this->checkoutAgreementsRepository->getList();

        foreach ($agreementsList as $agreement) {
            $agreementConfiguration[] = [
                'content' => $agreement->getIsHtml()
                    ? $agreement->getContent()
                    : nl2br($this->escaper->escapeHtml($agreement->getContent())),
                'height' => $agreement->getContentHeight(),
                'checkboxText' => $agreement->getCheckboxText()
            ];
        }
        $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
        ['children']['payments-list']['children']['before-place-order']['children']['checkout-agreements-modal']
        ['config']['agreementConfiguration'] = $agreementConfiguration;

        return $jsLayout;
    }
}
