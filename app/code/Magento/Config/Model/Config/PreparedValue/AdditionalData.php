<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\PreparedValue;

use Magento\Directory\Model\Currency;
use Magento\Config\Model\Config\Backend\Currency\AbstractCurrency;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;

/**
 * Adds additional data to backendModel.
 */
class AdditionalData
{
    /**
     * The application config storage.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig The application config storage.
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Adds additional data to backendModel if needed.
     *
     * Additional data, such as groups is coming within form request.
     * There is no possibility to retrieve this data separately, so
     * this emulation should be performed to preserve backward compatibility.
     *
     * @param Value $backendModel Instance of Value
     * @return void
     */
    public function apply(Value $backendModel)
    {
        // sets allowed currencies before save base, default or allow currency value
        if ($backendModel instanceof AbstractCurrency) {
            if ($backendModel->getPath() == Currency::XML_PATH_CURRENCY_ALLOW) {
                $allowedCurrencies = $backendModel->getValue();
            } else {
                $allowedCurrencies = $this->scopeConfig->getValue(
                    Currency::XML_PATH_CURRENCY_ALLOW,
                    $backendModel->getScope(),
                    $backendModel->getScopeId()
                );
            }
            $backendModel->addData([
                'groups' => [
                    'options' => [
                        'fields' => [
                            'allow' => ['value' => explode(',', $allowedCurrencies)]
                        ]
                    ]
                ]
            ]);
        }
    }
}
