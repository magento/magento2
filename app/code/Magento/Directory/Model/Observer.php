<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Directory module observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Directory\Model;

/**
 * Class \Magento\Directory\Model\Observer
 *
 */
class Observer
{
    const CRON_STRING_PATH = 'crontab/default/jobs/currency_rates_update/schedule/cron_expr';

    const IMPORT_ENABLE = 'currency/import/enabled';

    const IMPORT_SERVICE = 'currency/import/service';

    const XML_PATH_ERROR_TEMPLATE = 'currency/import/error_email_template';

    const XML_PATH_ERROR_IDENTITY = 'currency/import/error_email_identity';

    const XML_PATH_ERROR_RECIPIENT = 'currency/import/error_email';

    /**
     * @var \Magento\Directory\Model\Currency\Import\Factory
     */
    protected $_importFactory;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @param \Magento\Directory\Model\Currency\Import\Factory $importFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     */
    public function __construct(
        \Magento\Directory\Model\Currency\Import\Factory $importFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    ) {
        $this->_importFactory = $importFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_currencyFactory = $currencyFactory;
        $this->inlineTranslation = $inlineTranslation;
    }

    /**
     * @param mixed $schedule
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function scheduledUpdateCurrencyRates($schedule)
    {
        $importWarnings = [];
        if (!$this->_scopeConfig->getValue(
            self::IMPORT_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) || !$this->_scopeConfig->getValue(
            self::CRON_STRING_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            return;
        }

        $errors = [];
        $rates = [];
        $service = $this->_scopeConfig->getValue(
            self::IMPORT_SERVICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($service) {
            try {
                $importModel = $this->_importFactory->create($service);
                $rates = $importModel->fetchRates();
                $errors = $importModel->getMessages();
            } catch (\Exception $e) {
                $importWarnings[] = __('FATAL ERROR:') . ' ' . __('We can\'t initialize the import model.');
                throw $e;
            }
        } else {
            $importWarnings[] = __('FATAL ERROR:') . ' ' . __('Please specify the correct Import Service.');
        }

        if (sizeof($errors) > 0) {
            foreach ($errors as $error) {
                $importWarnings[] = __('WARNING:') . ' ' . $error;
            }
        }

        if (sizeof($importWarnings) == 0) {
            $this->_currencyFactory->create()->saveRates($rates);
        } else {
            $this->inlineTranslation->suspend();

            $this->_transportBuilder->setTemplateIdentifier(
                $this->_scopeConfig->getValue(
                    self::XML_PATH_ERROR_TEMPLATE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setTemplateOptions(
                [
                    'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )->setTemplateVars(
                ['warnings' => join("\n", $importWarnings)]
            )->setFrom(
                $this->_scopeConfig->getValue(
                    self::XML_PATH_ERROR_IDENTITY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->addTo(
                $this->_scopeConfig->getValue(
                    self::XML_PATH_ERROR_RECIPIENT,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();

            $this->inlineTranslation->resume();
        }
    }
}
