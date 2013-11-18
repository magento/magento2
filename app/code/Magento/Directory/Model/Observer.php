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
 * @category    Magento
 * @package     Magento_Directory
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
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
    const CRON_STRING_PATH = 'crontab/jobs/currency_rates_update/schedule/cron_expr';
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
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Core\Model\Translate
     */
    protected $_translate;

    /**
     * @var \Magento\Core\Model\Email\TemplateFactory
     */
    protected $_emailTemplateFactory;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @param \Magento\Directory\Model\Currency\Import\Factory $importFactory
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Core\Model\Translate $translate
     * @param \Magento\Core\Model\Email\TemplateFactory $emailTemplateFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     */
    public function __construct(
        \Magento\Directory\Model\Currency\Import\Factory $importFactory,
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Core\Model\Translate $translate,
        \Magento\Core\Model\Email\TemplateFactory $emailTemplateFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory
    ) {
        $this->_importFactory = $importFactory;
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_translate = $translate;
        $this->_emailTemplateFactory = $emailTemplateFactory;
        $this->_storeManager = $storeManager;
        $this->_currencyFactory = $currencyFactory;
    }

    public function scheduledUpdateCurrencyRates($schedule)
    {
        $importWarnings = array();
        if (!$this->_coreStoreConfig->getConfig(self::IMPORT_ENABLE)
            || !$this->_coreStoreConfig->getConfig(self::CRON_STRING_PATH)
        ) {
            return;
        }

        $service = $this->_coreStoreConfig->getConfig(self::IMPORT_SERVICE);
        if( !$service ) {
            $importWarnings[] = __('FATAL ERROR:') . ' ' . __('Please specify the correct Import Service.');
        }

        try {
            $importModel = $this->_importFactory->create($service);
        } catch (\Exception $e) {
            $importWarnings[] = __('FATAL ERROR:') . ' ' . __('We can\'t initialize the import model.');
        }

        $rates = $importModel->fetchRates();
        $errors = $importModel->getMessages();

        if( sizeof($errors) > 0 ) {
            foreach ($errors as $error) {
                $importWarnings[] = __('WARNING:') . ' ' . $error;
            }
        }

        if (sizeof($importWarnings) == 0) {
            $this->_currencyFactory->create()->saveRates($rates);
        } else {
            $this->_translate->setTranslateInline(false);

            /* @var $mailTemplate \Magento\Core\Model\Email\Template */
            $mailTemplate = $this->_emailTemplateFactory->create();
            $mailTemplate->setDesignConfig(array(
                'area' => \Magento\Core\Model\App\Area::AREA_FRONTEND,
                'store' => $this->_storeManager->getStore()->getId()
            ))
                ->sendTransactional(
                    $this->_coreStoreConfig->getConfig(self::XML_PATH_ERROR_TEMPLATE),
                    $this->_coreStoreConfig->getConfig(self::XML_PATH_ERROR_IDENTITY),
                    $this->_coreStoreConfig->getConfig(self::XML_PATH_ERROR_RECIPIENT),
                    null,
                    array('warnings'    => join("\n", $importWarnings),
                )
            );
            $this->_translate->setTranslateInline(true);
        }
    }
}
