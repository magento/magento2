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
 * @package     Magento_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Session;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Session\Config
     */
    protected $_model;

    /**
     * @var string
     */
    protected $_cacheLimiter = 'private_no_expire';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $sessionManager \Magento\Session\SessionManager */
        $sessionManager = $this->_objectManager->get('Magento\Session\SessionManager');
        if ($sessionManager->isSessionExists()) {
            $sessionManager->destroy();
        }
        $this->_model = $this->_objectManager->create(
            'Magento\Core\Model\Session\Config',
            array('saveMethod' => 'files', 'cacheLimiter' => $this->_cacheLimiter)
        );
    }

    protected function tearDown()
    {
        $this->_objectManager->removeSharedInstance('Magento\Core\Model\Session\Config');
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testDefaultConfiguration()
    {
        $this->assertEquals(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                'Magento\App\Filesystem'
            )->getPath(
                'session'
            ),
            $this->_model->getSavePath()
        );
        $this->assertEquals(
            \Magento\Core\Model\Session\Config::COOKIE_LIFETIME_DEFAULT,
            $this->_model->getCookieLifetime()
        );
        $this->assertEquals($this->_cacheLimiter, $this->_model->getCacheLimiter());
        $this->assertEquals('/', $this->_model->getCookiePath());
        $this->assertEquals('localhost', $this->_model->getCookieDomain());
        $this->assertEquals(false, $this->_model->getCookieSecure());
        $this->assertEquals(true, $this->_model->getCookieHttpOnly());
        $this->assertEquals($this->_model->getOption('save_path'), ini_get('session.save_path'));
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testGetSessionSaveMethod()
    {
        $this->assertEquals('files', $this->_model->getSaveHandler());
    }
}
