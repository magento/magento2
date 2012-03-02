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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Email information model
 * Email message may contain addresses in any of these three fields:
 *  -To:  Primary recipients
 *  -Cc:  Carbon copy to secondary recipients and other interested parties
 *  -Bcc: Blind carbon copy to tertiary recipients who receive the message
 *        without anyone else (including the To, Cc, and Bcc recipients) seeing who the tertiary recipients are
 *
 * @category    Mage
 * @package     Mage_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Core_Model_Email_Info extends Varien_Object
{
    /**
     * Name list of "Bcc" recipients
     *
     * @var array
     */
    protected $_bccNames = array();

    /**
     * Email list of "Bcc" recipients
     *
     * @var array
     */
    protected $_bccEmails = array();

    /**
     * Name list of "To" recipients
     *
     * @var array
     */
    protected $_toNames = array();

    /**
     * Email list of "To" recipients
     *
     * @var array
     */
    protected $_toEmails = array();


    /**
     * Add new "Bcc" recipient to current email
     *
     * @param string $email
     * @param string|null $name
     * @return Mage_Core_Model_Email_Info
     */
    public function addBcc($email, $name = null)
    {
        array_push($this->_bccNames, $name);
        array_push($this->_bccEmails, $email);
        return $this;
    }

    /**
     * Add new "To" recipient to current email
     *
     * @param string $email
     * @param string|null $name
     * @return Mage_Core_Model_Email_Info
     */
    public function addTo($email, $name = null)
    {
        array_push($this->_toNames, $name);
        array_push($this->_toEmails, $email);
        return $this;
    }

    /**
     * Get the name list of "Bcc" recipients
     *
     * @return array
     */
    public function getBccNames()
    {
        return $this->_bccNames;
    }

    /**
     * Get the email list of "Bcc" recipients
     *
     * @return array
     */
    public function getBccEmails()
    {
        return $this->_bccEmails;
    }

    /**
     * Get the name list of "To" recipients
     *
     * @return array
     */
    public function getToNames()
    {
        return $this->_toNames;
    }

    /**
     * Get the email list of "To" recipients
     *
     * @return array
     */
    public function getToEmails()
    {
        return $this->_toEmails;
    }
}
