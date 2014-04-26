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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Install\Model;

class InstallerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Install\Model\Installer
     */
    protected $_model;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * Application chache model
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $_cache;

    /**
     * Application config model
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Install\Model\Installer\Config
     */
    protected $_installerConfig;

    /**
     * Set up before test
     */
    public function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_cache = $this->getMock('\Magento\Framework\App\CacheInterface', array(), array(), '', false);
        $this->_config =
            $this->getMock('\Magento\Framework\App\Config\ReinitableConfigInterface', array(), array(), '', false);
        $this->_cacheState = $this->getMock('\Magento\Framework\App\Cache\StateInterface', array(), array(), '', false);
        $this->_cacheTypeList =
            $this->getMock('\Magento\Framework\App\Cache\TypeListInterface', array(), array(), '', false);
        $this->_appState = $this->getMock('\Magento\Framework\App\State', array(), array(), '', false);
        $this->_installerConfig = $this->getMock(
            '\Magento\Install\Model\Installer\Config',
            array(),
            array(),
            '',
            false
        );

        $this->_model = $this->_objectManager->getObject(
            'Magento\Install\Model\Installer',
            array(
                'cache' => $this->_cache,
                'config' => $this->_config,
                'cacheState' => $this->_cacheState,
                'cacheTypeList' => $this->_cacheTypeList,
                'appState' => $this->_appState,
                'installerConfig' => $this->_installerConfig
            )
        );
    }

    public function testFinish()
    {
        $cacheTypeListArray = array('one', 'two');

        $this->_cache->expects($this->once())->method('clean');

        $this->_config->expects($this->once())->method('reinit');

        $this->_cacheState->expects($this->once())->method('persist');
        $this->_cacheState->expects($this->exactly(count($cacheTypeListArray)))->method('setEnabled');

        $this->_cacheTypeList->expects(
            $this->once()
        )->method(
            'getTypes'
        )->will(
            $this->returnValue($cacheTypeListArray)
        );

        $this->_appState->expects($this->once())->method('setInstallDate')->with($this->greaterThanOrEqual(date('r')));

        $this->_installerConfig->expects(
            $this->once()
        )->method(
            'replaceTmpInstallDate'
        )->with(
            $this->greaterThanOrEqual(date('r'))
        );

        $this->assertSame($this->_model, $this->_model->finish());
    }
}
