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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PayPal specific model for certificate based authentication
 */
class Mage_Paypal_Model_Cert extends Mage_Core_Model_Abstract
{
    /**
     * Certificate base path
     */
    const BASEPATH_PAYPAL_CERT  = 'cert/paypal';

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        $this->_init('Mage_Paypal_Model_Resource_Cert');
    }

    /**
     * Load model by website id
     *
     * @param int $websiteId
     * @param bool $strictLoad
     * @return Mage_Paypal_Model_Cert
     */
    public function loadByWebsite($websiteId, $strictLoad = true)
    {
        $this->setWebsiteId($websiteId);
        $this->_getResource()->loadByWebsite($this, $strictLoad);
        return $this;
    }

    /**
     * Get path to PayPal certificate file, if file does not exist try to create it
     *
     * @return string
     */
    public function getCertPath()
    {
        if (!$this->getContent()) {
            Mage::throwException(Mage::helper('Mage_Paypal_Helper_Data')->__('PayPal certificate does not exist.'));
        }

        $certFileName = sprintf('cert_%s_%s.pem', $this->getWebsiteId(), strtotime($this->getUpdatedAt()));
        $certFile = $this->_getBaseDir() . DS . $certFileName;

        if (!file_exists($certFile)) {
            $this->_createCertFile($certFile);
        }
        return $certFile;
    }

    /**
     * Create physical certificate file based on DB data
     *
     * @param string $file
     */
    protected function _createCertFile($file)
    {
        $certDir = $this->_getBaseDir();
        if (!is_dir($certDir)) {
            $ioAdapter = new Varien_Io_File();
            $ioAdapter->checkAndCreateFolder($certDir);
        } else {
            $this->_removeOutdatedCertFile();
        }

        file_put_contents($file, Mage::helper('Mage_Core_Helper_Data')->decrypt($this->getContent()));
    }

    /**
     * Check and remove outdated certificate file by website
     *
     * @return void
     */
    protected function _removeOutdatedCertFile()
    {
        $certDir = $this->_getBaseDir();
        if (is_dir($certDir)) {
            $entries = scandir($certDir);
            foreach ($entries as $entry) {
                if ($entry != '.' && $entry != '..' && strpos($entry, 'cert_' . $this->getWebsiteId()) !== false) {
                    unlink($certDir . DS . $entry);
                }
            }
        }
    }

    /**
     * Retrieve base directory for certificate
     *
     * @return string
     */
    protected function _getBaseDir()
    {
        return Mage::getBaseDir('var') . DS . self::BASEPATH_PAYPAL_CERT;
    }

    /**
     * Delete assigned certificate file after delete object
     *
     * @return Mage_Paypal_Model_Cert
     */
    protected function _afterDelete()
    {
        $this->_removeOutdatedCertFile();
        return $this;
    }
}
