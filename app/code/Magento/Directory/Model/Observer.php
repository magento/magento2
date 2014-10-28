<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Directory module observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Directory\Model;

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
     * @var \Magento\Framework\StoreManagerInterface
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
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     */
    public function __construct(
        \Magento\Directory\Model\Currency\Import\Factory $importFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
    ) {
        $this->_importFactory = $importFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_importFactory = $importFactory;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_currencyFactory = $currencyFactory;
        $this->inlineTranslation = $inlineTranslation;
    }

    /**
     * @param mixed $schedule
     * @return void
     */
    public function scheduledUpdateCurrencyRates($schedule)
    {
        $importWarnings = array();
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

        $errors = array();
        $rates = array();
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
                array(
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId()
                )
            )->setTemplateVars(
                array('warnings' => join("\n", $importWarnings))
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
