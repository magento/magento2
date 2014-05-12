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
namespace Magento\PageCache\Model;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_coreConfigMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * setUp all mocks and data function
     */
    public function setUp()
    {
        $filesystemMock =
            $this->getMock('Magento\Framework\App\Filesystem', array('getDirectoryRead'), array(), '', false);
        $this->_coreConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_cacheState =
            $this->getMock('\Magento\Framework\App\Cache\State', array('isEnabled'), array(), '', false);

        $modulesDirectoryMock = $this->getMock(
            'Magento\Framework\Filesystem\Directory\Write',
            array(),
            array(),
            '',
            false
        );
        $filesystemMock->expects(
            $this->once()
        )->method(
            'getDirectoryRead'
        )->with(
            \Magento\Framework\App\Filesystem::MODULES_DIR
        )->will(
            $this->returnValue($modulesDirectoryMock)
        );
        $modulesDirectoryMock->expects(
            $this->any()
        )->method(
            'readFile'
        )->will(
            $this->returnValue(file_get_contents(__DIR__ . '/_files/test.vcl'))
        );
        $this->_coreConfigMock->expects(
            $this->any()
        )->method(
            'getValue'
        )->will(
            $this->returnValueMap(
                array(
                    array(
                        \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_BACKEND_HOST,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        null,
                        'example.com'
                    ),
                    array(
                        \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_BACKEND_PORT,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        null,
                        '8080'
                    ),
                    array(
                        \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_ACCESS_LIST,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        null,
                        '127.0.0.1, 192.168.0.1'
                    ),
                    array(
                        \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_DESIGN_THEME_REGEX,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        null,
                        serialize(array(array('regexp' => '(?i)pattern', 'value' => 'value_for_pattern')))
                    )
                )
            )
        );

        $this->_model = new \Magento\PageCache\Model\Config(
            $filesystemMock,
            $this->_coreConfigMock,
            $this->_cacheState
        );
    }

    /**
     * test for getVcl method
     */
    public function testGetVcl()
    {
        $test = $this->_model->getVclFile();
        $this->assertEquals(file_get_contents(__DIR__ . '/_files/result.vcl'), $test);
    }

    public function testGetTll()
    {
        $this->_coreConfigMock->expects($this->once())->method('getValue')->with(Config::XML_PAGECACHE_TTL);
        $this->_model->getTtl();
    }

    /**
     * Whether a cache type is enabled
     */
    public function testIsEnabled()
    {
        $this->_cacheState->setEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER, true);

        $this->_cacheState->expects(
            $this->once()
        )->method(
            'isEnabled'
        )->with(
            \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
        )->will(
            $this->returnValue(true)
        );
        $this->_model->isEnabled();
    }
}
