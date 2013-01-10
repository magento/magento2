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
 * @category    Mage
 * @package     Mage_Directory
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Directory module observer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Directory_Model_Observer
{
    const CRON_STRING_PATH = 'crontab/jobs/currency_rates_update/schedule/cron_expr';
    const IMPORT_ENABLE = 'currency/import/enabled';
    const IMPORT_SERVICE = 'currency/import/service';

    const XML_PATH_ERROR_TEMPLATE = 'currency/import/error_email_template';
    const XML_PATH_ERROR_IDENTITY = 'currency/import/error_email_identity';
    const XML_PATH_ERROR_RECIPIENT = 'currency/import/error_email';

    public function scheduledUpdateCurrencyRates($schedule)
    {
        $importWarnings = array();
        if(!Mage::getStoreConfig(self::IMPORT_ENABLE) || !Mage::getStoreConfig(self::CRON_STRING_PATH)) {
            return;
        }

        $service = Mage::getStoreConfig(self::IMPORT_SERVICE);
        if( !$service ) {
            $importWarnings[] = Mage::helper('Mage_Directory_Helper_Data')->__('FATAL ERROR:') . ' ' . Mage::helper('Mage_Directory_Helper_Data')->__('Invalid Import Service specified.');
        }

        try {
            $importModel = Mage::getModel(
                Mage::getConfig()->getNode('global/currency/import/services/' . $service . '/model')->asArray()
            );
        } catch (Exception $e) {
            $importWarnings[] = Mage::helper('Mage_Directory_Helper_Data')->__('FATAL ERROR:') . ' ' . Mage::throwException(Mage::helper('Mage_Directory_Helper_Data')->__('Unable to initialize the import model.'));
        }

        $rates = $importModel->fetchRates();
        $errors = $importModel->getMessages();

        if( sizeof($errors) > 0 ) {
            foreach ($errors as $error) {
                $importWarnings[] = Mage::helper('Mage_Directory_Helper_Data')->__('WARNING:') . ' ' . $error;
            }
        }

        if (sizeof($importWarnings) == 0) {
            Mage::getModel('Mage_Directory_Model_Currency')->saveRates($rates);
        }
        else {
            $translate = Mage::getSingleton('Mage_Core_Model_Translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);

            /* @var $mailTemplate Mage_Core_Model_Email_Template */
            $mailTemplate = Mage::getModel('Mage_Core_Model_Email_Template');
            $mailTemplate->setDesignConfig(array(
                'area' => Mage_Core_Model_App_Area::AREA_FRONTEND,
                'store' => Mage::app()->getStore()->getId()
            ))
                ->sendTransactional(
                    Mage::getStoreConfig(self::XML_PATH_ERROR_TEMPLATE),
                    Mage::getStoreConfig(self::XML_PATH_ERROR_IDENTITY),
                    Mage::getStoreConfig(self::XML_PATH_ERROR_RECIPIENT),
                    null,
                    array('warnings'    => join("\n", $importWarnings),
                )
            );

            $translate->setTranslateInline(true);
        }
    }
}
