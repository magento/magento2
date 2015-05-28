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
        $form = [];
        $agreementsList = $this->checkoutAgreementsRepository->getList();
        foreach ($agreementsList as $agreement) {
            $name = $agreement->getAgreementId();
            $form[$name] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'checkoutAgreements',
                    'customEntry' => 'checkoutAgreements.' . $name,
                    'template' => 'Magento_CheckoutAgreements/form/element/agreement'
                ],
                'agreementConfiguration' => [
                    'content' => $agreement->getIsHtml()
                        ? $agreement->getContent()
                        : nl2br($this->escaper->escapeHtml($agreement->getContent())),
                    'height' => $agreement->getContentHeight(),
                    'checkboxText' => $agreement->getCheckboxText()
                ],
                'dataScope' => $name,
                'provider' => 'checkoutProvider',
                'validation' => ['checked' => true],
                'customEntry' => null,
                'visible' => true
            ];
        }
        $result['components']['checkout']['children']['steps']['children']['review']['children']
        ['beforePlaceOrder']['children']['checkoutAgreements']['children'] = $form;

        return array_merge_recursive($jsLayout, $result);
    }
}
