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
namespace Magento\Framework\App\Filesystem\DirectoryList;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Configuration
     */
    protected $dirListConfiguration;

    /**
     * @dataProvider configureDataProvider
     */
    public function testConfigure($pubDirIsConfigured)
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        /* Mock Config model */
        $config = $this->getMockBuilder(
            'Magento\Framework\App\Config\ScopeConfigInterface'
        )->disableOriginalConstructor()->setMethods(
            array('getValue', 'setValue', 'isSetFlag')
        )->getMock();

        $config->expects(
            $this->at(0)
        )->method(
            'getValue'
        )->with(
            Configuration::XML_FILESYSTEM_DIRECTORY_PATH
        )->will(
            $this->returnValue(array(\Magento\Framework\App\Filesystem::PUB_DIR => array('uri' => '')))
        );

        $config->expects(
            $this->at(1)
        )->method(
            'getValue'
        )->with(
            Configuration::XML_FILESYSTEM_WRAPPER_PATH
        )->will(
            $this->returnValue(array(\Magento\Framework\Filesystem::HTTP => array('protocol' => 'http')))
        );

        /* Mock DirectoryList model */
        $directoryList = $this->getMockBuilder(
            'Magento\Framework\Filesystem\DirectoryList'
        )->disableOriginalConstructor()->setMethods(
            array('setDirectory', 'isConfigured', 'addProtocol', 'getConfig')
        )->getMock();

        $directoryList->expects(
            $this->once()
        )->method(
            'addProtocol'
        )->with(
            \Magento\Framework\Filesystem::HTTP,
            array('protocol' => 'http')
        );

        $directoryList->expects(
            $this->atLeastOnce()
        )->method(
            'isConfigured'
        )->with(
            \Magento\Framework\App\Filesystem::PUB_DIR
        )->will(
            $this->returnValue($pubDirIsConfigured)
        );

        if ($pubDirIsConfigured) {
            $directoryList->expects($this->once())
                ->method('getConfig')
                ->with(\Magento\Framework\App\Filesystem::PUB_DIR)
                ->will($this->returnValue(['test_key' => 'test_value']));
            $directoryList->expects($this->once())
                ->method('setDirectory')
                ->with(\Magento\Framework\App\Filesystem::PUB_DIR, ['uri' => '', 'test_key' => 'test_value']);
        } else {
            $directoryList->expects($this->once())
                ->method('setDirectory')
                ->with(\Magento\Framework\App\Filesystem::PUB_DIR, array('uri' => ''));
        }

        $this->dirListConfiguration = $objectManager->getObject(
            'Magento\Framework\App\Filesystem\DirectoryList\Configuration',
            array('config' => $config)
        );
        $this->assertNull($this->dirListConfiguration->configure($directoryList));
    }

    public function configureDataProvider()
    {
        return array(array('pubDirIsConfigured' => true), array('pubDirIsConfigured' => false));
    }
}
