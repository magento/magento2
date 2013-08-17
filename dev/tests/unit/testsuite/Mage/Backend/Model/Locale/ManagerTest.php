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
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Model_Locale_ManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Locale_Manager
     */
    protected $_model;

    /**
     * @var Mage_Core_Model_Translate
     */
    protected $_translator;

    /**
     * @var Mage_Backend_Model_Session
     */
    protected $_session;

    /**
     * @var Mage_Backend_Model_Auth_Session
     */
    protected $_authSession;

    public function setUp()
    {
        $this->_session = $this->getMock('Mage_Backend_Model_Session', array(), array(), '', false);

        $this->_authSession = $this->getMock('Mage_Backend_Model_Auth_Session',
            array('getUser'), array(), '', false);

        $userMock = new Varien_Object();

        $this->_authSession->expects($this->any())
            ->method('getUser')
            ->will($this->returnValue($userMock));

        $this->_translator = $this->getMock('Mage_Core_Model_Translate',
            array(), array(), '', false);

        $this->_translator->expects($this->any())
            ->method('setLocale')
            ->will($this->returnValue($this->_translator));

        $this->_translator->expects($this->any())
            ->method('init')
            ->will($this->returnValue(false));

        $this->_model = new Mage_Backend_Model_Locale_Manager($this->_session, $this->_authSession, $this->_translator);
    }

    /**
     * @return array
     */
    public function switchBackendInterfaceLocaleDataProvider()
    {
        return array(
            'case1' => array(
                'locale' => 'de_DE',
            ),
            'case2' => array(
                'locale' => 'en_US',
            ),
        );
    }

    /**
     * @param string $locale
     * @dataProvider switchBackendInterfaceLocaleDataProvider
     * @covers Mage_Backend_Model_Locale_Manager::switchBackendInterfaceLocale
     */
    public function testSwitchBackendInterfaceLocale($locale)
    {
        $this->_model->switchBackendInterfaceLocale($locale);

        $userInterfaceLocale = $this->_authSession->getUser()->getInterfaceLocale();
        $this->assertEquals($userInterfaceLocale, $locale);

        $sessionLocale = $this->_session->getSessionLocale();
        $this->assertEquals($sessionLocale, null);
    }

    /**
     * @covers Mage_Backend_Model_Locale_Manager::getUserInterfaceLocale
     */
    public function testGetUserInterfaceLocaleDefault()
    {
        $locale = $this->_model->getUserInterfaceLocale();

        $this->assertEquals($locale, Mage_Core_Model_LocaleInterface::DEFAULT_LOCALE);
    }

    /**
     * @covers Mage_Backend_Model_Locale_Manager::getUserInterfaceLocale
     */
    public function testGetUserInterfaceLocale()
    {
        $this->_model->switchBackendInterfaceLocale('de_DE');
        $locale = $this->_model->getUserInterfaceLocale();

        $this->assertEquals($locale, 'de_DE');
    }
}
