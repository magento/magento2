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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Common sender
 *
 * @category   Mage
 * @package    Mage_Core
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Sender
{
    /** @var Mage_Core_Model_Email_Template_Mailer */
    protected $_mailer;

    /** @var Mage_Core_Model_Email_Info */
    protected $_emailInfo;

    /** @var Mage_Core_Model_Store */
    protected $_store;

    /**
     * @param Mage_Core_Model_Email_Template_Mailer $mailer
     * @param Mage_Core_Model_Email_Info $info
     * @param Mage_Core_Model_Store $store
     */
    public function __construct(Mage_Core_Model_Email_Template_Mailer $mailer,
        Mage_Core_Model_Email_Info $info, Mage_Core_Model_Store $store
    ) {
        $this->_mailer = $mailer;
        $this->_emailInfo = $info;
        $this->_store = $store;
    }

    /**
     * @param string $email
     * @param string $name
     * @param string $template
     * @param string $sender
     * @param array $templateParams
     * @param int $storeId
     * @return Mage_Core_Model_Sender
     */
    public function send($email, $name, $template, $sender, $templateParams = array(), $storeId)
    {
        $this->_store->load($storeId);
        $this->_emailInfo->addTo($email, $name);
        $this->_mailer->addEmailInfo($this->_emailInfo);
        $this->_mailer->setSender($this->_store->getConfig($sender, $this->_store->getId()));
        $this->_mailer->setStoreId($this->_store->getId());
        $this->_mailer->setTemplateId($this->_store->getConfig($template, $this->_store->getId()));
        $this->_mailer->setTemplateParams($templateParams);
        $this->_mailer->send();
        return $this;
    }
}
