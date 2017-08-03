<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Framework\Locale\Bundle\DataBundle;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Store\Model\ScopeInterface;

/**
 * Payment configuration model
 *
 * Used for retrieving configuration data by payment models
 *
 * @api
 * @since 2.0.0
 */
class Config
{
    /**
     * Years range
     */
    const YEARS_RANGE = 10;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_methods;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Config\DataInterface
     * @since 2.0.0
     */
    protected $_dataStorage;

    /**
     * Locale model
     *
     * @var \Magento\Framework\Locale\ResolverInterface
     * @since 2.0.0
     */
    protected $localeResolver;

    /**
     * Payment method factory
     *
     * @var \Magento\Payment\Model\Method\Factory
     * @since 2.0.0
     */
    protected $_paymentMethodFactory;

    /**
     * DateTime
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     * @since 2.0.0
     */
    protected $_date;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Factory $paymentMethodFactory
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Framework\Config\DataInterface $dataStorage
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Factory $paymentMethodFactory,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Config\DataInterface $dataStorage,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_dataStorage = $dataStorage;
        $this->_paymentMethodFactory = $paymentMethodFactory;
        $this->localeResolver = $localeResolver;
        $this->_date = $date;
    }

    /**
     * Retrieve active system payments
     *
     * @return array
     * @api
     * @since 2.0.0
     */
    public function getActiveMethods()
    {
        $methods = [];
        foreach ($this->_scopeConfig->getValue('payment', ScopeInterface::SCOPE_STORE, null) as $code => $data) {
            if (isset($data['active'], $data['model']) && (bool)$data['active']) {
                /** @var MethodInterface $methodModel Actually it's wrong interface */
                $methodModel = $this->_paymentMethodFactory->create($data['model']);
                $methodModel->setStore(null);
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
     * @api
     * @since 2.0.0
     */
    public function getCcTypes()
    {
        return $this->_dataStorage->get('credit_cards');
    }

    /**
     * Retrieve array of payment methods information
     *
     * @return array
     * @api
     * @since 2.0.0
     */
    public function getMethodsInfo()
    {
        return $this->_dataStorage->get('methods');
    }

    /**
     * Get payment groups
     *
     * @return array
     * @api
     * @since 2.0.0
     */
    public function getGroups()
    {
        return $this->_dataStorage->get('groups');
    }

    /**
     * Retrieve list of months translation
     *
     * @return array
     * @api
     * @since 2.0.0
     */
    public function getMonths()
    {
        $data = [];
        $months = (new DataBundle())->get(
            $this->localeResolver->getLocale()
        )['calendar']['gregorian']['monthNames']['format']['wide'];
        foreach ($months as $key => $value) {
            $monthNum = ++$key < 10 ? '0' . $key : $key;
            $data[$key] = $monthNum . ' - ' . $value;
        }
        return $data;
    }

    /**
     * Retrieve array of available years
     *
     * @return array
     * @api
     * @since 2.0.0
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
