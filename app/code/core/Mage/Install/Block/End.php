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
 * @package     Mage_Install
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Installation ending block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Install_Block_End extends Mage_Install_Block_Abstract
{
    protected $_template = 'end.phtml';

    public function getEncryptionKey()
    {
        $key = $this->getData('encryption_key');
        if ($key === null) {
            $key = (string) Mage::getConfig()->getNode('global/crypt/key');
            $this->setData('encryption_key', $key);
        }
        return $key;
    }

    /**
     * Return url for iframe source
     *
     * @return string
     */
    public function getIframeSourceUrl()
    {
        if (!Mage_AdminNotification_Model_Survey::isSurveyUrlValid()
            || Mage::getSingleton('Mage_Install_Model_Installer')->getHideIframe()) {
            return null;
        }
        return Mage_AdminNotification_Model_Survey::getSurveyUrl();
    }
}
