<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Directory\Model;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Directory\Model\Currency\Import\Factory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Import currency rates
 */
class Observer
{
    public const CRON_STRING_PATH = 'crontab/default/jobs/currency_rates_update/schedule/cron_expr';

    public const IMPORT_ENABLE = 'currency/import/enabled';

    public const IMPORT_SERVICE = 'currency/import/service';

    public const XML_PATH_ERROR_TEMPLATE = 'currency/import/error_email_template';

    public const XML_PATH_ERROR_IDENTITY = 'currency/import/error_email_identity';

    public const XML_PATH_ERROR_RECIPIENT = 'currency/import/error_email';

    /**
     * @var Factory
     */
    protected $_importFactory;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;

    /**
     * @param Factory $importFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param StateInterface $inlineTranslation
     */
    public function __construct(
        Factory $importFactory,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        StateInterface $inlineTranslation
    ) {
        $this->_importFactory = $importFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_currencyFactory = $currencyFactory;
        $this->inlineTranslation = $inlineTranslation;
    }

    /**
     * Schedule update currency rates
     *
     * @param mixed $schedule
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function scheduledUpdateCurrencyRates($schedule)
    {
        $importWarnings = [];
        if (!$this->_scopeConfig->getValue(
            self::IMPORT_ENABLE,
            ScopeInterface::SCOPE_STORE
        ) || !$this->_scopeConfig->getValue(
            self::CRON_STRING_PATH,
            ScopeInterface::SCOPE_STORE
        )
        ) {
            return;
        }

        $errors = [];
        $rates = [];
        $service = $this->_scopeConfig->getValue(
            self::IMPORT_SERVICE,
            ScopeInterface::SCOPE_STORE
        );
        if ($service) {
            try {
                $importModel = $this->_importFactory->create($service);
                $rates = $importModel->fetchRates();
                $errors = $importModel->getMessages();
            } catch (\Exception $e) {
                $importWarnings[] = __('FATAL ERROR:') . ' '
                    . __("The import model can't be initialized. Verify the model and try again.");
                throw $e;
            }
        } else {
            $importWarnings[] = __('FATAL ERROR:') . ' ' . __('Please specify the correct Import Service.');
        }

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $importWarnings[] = __('WARNING:') . ' ' . $error;
            }
        }

        $errorRecipient = $this->_scopeConfig->getValue(
            self::XML_PATH_ERROR_RECIPIENT,
            ScopeInterface::SCOPE_STORE
        );
        $errorRecipients = !empty($errorRecipient) ? explode(',', $errorRecipient) : [];
        if (count($importWarnings) == 0) {
            $this->_currencyFactory->create()->saveRates($rates);
        } elseif (count($errorRecipients) > 0) {
            //if $errorRecipient is not set, there is no sense send email to nobody
            $this->inlineTranslation->suspend();

            $this->_transportBuilder->setTemplateIdentifier(
                $this->_scopeConfig->getValue(
                    self::XML_PATH_ERROR_TEMPLATE,
                    ScopeInterface::SCOPE_STORE
                )
            )->setTemplateOptions(
                [
                    'area' => FrontNameResolver::AREA_CODE,
                    'store' => Store::DEFAULT_STORE_ID,
                ]
            )->setTemplateVars(
                ['warnings' => join("\n", $importWarnings)]
            )->setFrom(
                $this->_scopeConfig->getValue(
                    self::XML_PATH_ERROR_IDENTITY,
                    ScopeInterface::SCOPE_STORE
                )
            )->addTo($errorRecipients);
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            $this->inlineTranslation->resume();
        }
    }
}
