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
 * @package     Mage_Backend
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @magentoAppArea adminhtml
 */
class Mage_Backend_Model_LocaleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_LocaleInterface
     */
    protected $_model;

    public function setUp()
    {
        parent::setUp();
        $this->_model = Mage::getModel('Mage_Backend_Model_Locale');
    }

    /**
     * @covers Mage_Core_Model_LocaleInterface::setLocale
     */
    public function testSetLocaleWithDefaultLocale()
    {
        $this->_checkSetLocale(Mage_Core_Model_LocaleInterface::DEFAULT_LOCALE);
    }

    /**
     * @covers Mage_Core_Model_LocaleInterface::setLocale
     */
    public function testSetLocaleWithBaseInterfaceLocale()
    {
        $user = new Varien_Object();
        $session = Mage::getSingleton('Mage_Backend_Model_Auth_Session');
        $session->setUser($user);
        Mage::getSingleton('Mage_Backend_Model_Auth_Session')->getUser()->setInterfaceLocale('fr_FR');
        $this->_checkSetLocale('fr_FR');
    }

    /**
     * @covers Mage_Core_Model_LocaleInterface::setLocale
     */
    public function testSetLocaleWithSessionLocale()
    {
        Mage::getSingleton('Mage_Backend_Model_Session')->setSessionLocale('es_ES');
        $this->_checkSetLocale('es_ES');
    }

    /**
     * @covers Mage_Core_Model_LocaleInterface::setLocale
     */
    public function testSetLocaleWithRequestLocale()
    {
        $request = Mage::app()->getRequest();
        $request->setPost(array('locale' => 'de_DE'));
        $this->_checkSetLocale('de_DE');
    }

    /**
     * Check set locale
     *
     * @param string $localeCodeToCheck
     * @return void
     */
    protected function _checkSetLocale($localeCodeToCheck)
    {
        $this->_model->setLocale();
        $localeCode = $this->_model->getLocaleCode();
        $this->assertEquals($localeCode, $localeCodeToCheck);
    }
}
