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
 * @package     Mage_Oauth
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * OAuth abstract authorization block
 *
 * @category   Mage
 * @package    Mage_Oauth
 * @author     Magento Core Team <core@magentocommerce.com>
 * @method string getToken()
 * @method Mage_Oauth_Block_AuthorizeBaseAbstract setToken() setToken(string $token)
 * @method boolean getIsSimple()
 * @method Mage_Oauth_Block_Authorize_Button setIsSimple() setIsSimple(boolean $flag)
 * @method boolean getHasException()
 * @method Mage_Oauth_Block_AuthorizeBaseAbstract setIsException() setHasException(boolean $flag)
 * @method boolean getVerifier()
 * @method Mage_Oauth_Block_AuthorizeBaseAbstract setVerifier() setVerifier(string $verifier)
 * @method boolean getIsLogged()
 * @method Mage_Oauth_Block_AuthorizeBaseAbstract setIsLogged() setIsLogged(boolean $flag)
 */
abstract class Mage_Oauth_Block_Authorize_Abstract extends Mage_Core_Block_Template
{
    /**
     * Helper
     *
     * @var Mage_Oauth_Helper_Data
     */
    protected $_helper;

    /**
     * Consumer model
     *
     * @var Mage_Oauth_Model_Consumer
     */
    protected $_consumer;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_helper = Mage::helper('Mage_Oauth_Helper_Data');
    }

    /**
     * Get consumer instance by token value
     *
     * @return Mage_Oauth_Model_Consumer
     */
    public function getConsumer()
    {
        if (null === $this->_consumer) {
            /** @var $token Mage_Oauth_Model_Token */
            $token = Mage::getModel('Mage_Oauth_Model_Token');
            $token->load($this->getToken(), 'token');
            $this->_consumer = $token->getConsumer();
        }
        return $this->_consumer;
    }

    /**
     * Get absolute path to template
     *
     * Load template from adminhtml/default area flag is_simple is set
     *
     * @return string
     */
    public function getTemplateFile()
    {
        if (!$this->getIsSimple()) {
            return parent::getTemplateFile();
        }

        //load base template from admin area
        $params = array(
            '_relative' => true,
            '_area'     => 'adminhtml',
            '_package'  => 'default'
        );
        return Mage::getDesign()->getTemplateFilename($this->getTemplate(), $params);
    }
}
