<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Store\Model\ScopeInterface;

/**
 * Payment configuration model
 *
 * Used for retrieving configuration data by payment models
 */
class Config
{
    /**
     * Years range
     */
    const YEARS_RANGE = 10;

    /**
     * @var array
     */
    protected $_methods;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Config\DataInterface
     */
    protected $_dataStorage;

    /**
     * Locale model
     *
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;

    /**
     * Payment method factory
     *
     * @var \Magento\Payment\Model\Method\Factory
     */
    protected $_paymentMethodFactory;

    /**
     * DateTime
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Factory $paymentMethodFactory
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param \Magento\Framework\Config\DataInterface $dataStorage
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Framework\Config\DataInterface $dataStorage,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_dataStorage = $dataStorage;
        $this->_paymentMethodFactory = $paymentMethodFactory;
        $this->_localeLists = $localeLists;
        $this->_date = $date;
    }

    /**
     * Retrieve active system payments
     *
     * @return array
     */
    public function getActiveMethods()
    {
        $methods = [];
        foreach ($this->_scopeConfig->getValue('payment', ScopeInterface::SCOPE_STORE, null) as $code => $data) {
            if (isset($data['active']) && (bool)$data['active'] && isset($data['model'])) {
                /** @var AbstractMethod|null $methodModel Actually it's wrong interface */
                $methodModel = $this->_paymentMethodFactory->create($data['model']);
                $methodModel->setId($code)->setStore(null);
                if ($methodModel->getConfigData('active', null)) {
                    $methods[$code] = $methodModel;
                }
            }
        }
        return $methods;
    }

    /**
     * Get list of credit card types
     *
     * @return array
     */
    public function getCcTypes()
    {
        return $this->_dataStorage->get('credit_cards');
    }

    /**
     * Retrieve array of payment methods information
     *
     * @return array
     */
    public function getMethodsInfo()
    {
        return $this->_dataStorage->get('methods');
    }

    /**
     * Get payment groups
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->_dataStorage->get('groups');
    }

    /**
     * Retrieve list of months translation
     *
     * @return array
     */
    public function getMonths()
    {
        $data = $this->_localeLists->getTranslationList('month');
        foreach ($data as $key => $value) {
            $monthNum = $key < 10 ? '0' . $key : $key;
            $data[$key] = $monthNum . ' - ' . $value;
        }
        return $data;
    }

    /**
     * Retrieve array of available years
     *
     * @return array
     */
    public function getYears()
    {
        $years = [];
        $first = (int)$this->_date->date('Y');
        for ($index = 0; $index <= self::YEARS_RANGE; $index++) {
            $year = $first + $index;
            $years[$year] = $year;
        }
        return $years;
    }
}
