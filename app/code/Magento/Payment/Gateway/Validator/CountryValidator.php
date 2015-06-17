<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Validator;

use Magento\Framework\Exception\NotFoundException;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

class CountryValidator implements ValidatorInterface
{
    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    private $config;

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @param \Magento\Payment\Gateway\ConfigInterface $config
     * @param ResultInterfaceFactory $resultFactory
     */
    public function __construct(
        ConfigInterface $config,
        ResultInterfaceFactory $resultFactory
    ) {
        $this->config = $config;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @param array $validationSubject
     * @return bool
     * @throws NotFoundException
     * @throws \Exception
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

        return $this->resultFactory->create(
            [
                'isValid' => $isValid
            ]
        );
    }
}
