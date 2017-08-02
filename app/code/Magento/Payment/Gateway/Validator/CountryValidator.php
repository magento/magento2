<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Validator;

use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Class CountryValidator
 * @package Magento\Payment\Gateway\Validator
 * @api
 * @since 2.0.0
 */
class CountryValidator extends AbstractValidator
{
    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     * @since 2.0.0
     */
    private $config;

    /**
     * @param ResultInterfaceFactory $resultFactory
     * @param \Magento\Payment\Gateway\ConfigInterface $config
     * @since 2.0.0
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        ConfigInterface $config
    ) {
        $this->config = $config;
        parent::__construct($resultFactory);
    }

    /**
     * @param array $validationSubject
     * @return bool
     * @throws NotFoundException
     * @throws \Exception
     * @since 2.0.0
     */
    public function validate(array $validationSubject)
    {
        $isValid = true;
        $storeId = $validationSubject['storeId'];

        if ((int)$this->config->getValue('allowspecific', $storeId) === 1) {
            $availableCountries = explode(
                ',',
                $this->config->getValue('specificcountry', $storeId)
            );

            if (!in_array($validationSubject['country'], $availableCountries)) {
                $isValid =  false;
            }
        }

        return $this->createResult($isValid);
    }
}
