<?php
/**
 * \Magento\Widget\Model\Config\FileResolver
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Widget\Model\Config;

class FileResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Widget\Model\Config\FileResolver
     */
    private $_object;

    /** @var \Magento\App\Dir/PHPUnit_Framework_MockObject_MockObject  */
    private $_applicationDirsMock;

    public function setUp()
    {
        $this->_applicationDirsMock = $this->getMockBuilder('Magento\App\Dir')
            ->disableOriginalConstructor()
            ->getMock();

        $moduleListMock = $this->getMockBuilder('Magento\App\ModuleListInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $moduleListMock->expects($this->any())
            ->method('getModules')
            ->will($this->returnValue(array('Magento_Test' => array(
                'name' => 'Magento_Test',
                'version' => '1.11.1',
                'active' => 'true'
            ))));

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $moduleReader = $objectManager->create('Magento\Core\Model\Config\Modules\Reader', array(
            'moduleList' => $moduleListMock
        ));
        $moduleReader->setModuleDir('Magento_Test', 'etc', __DIR__ . '/_files/code/Magento/Test/etc');
        $this->_object = $objectManager->create('Magento\Widget\Model\Config\FileResolver', array(
            'moduleReader' => $moduleReader,
            'applicationDirs' => $this->_applicationDirsMock
        ));
    }

    public function testGetDesign()
    {
        $this->_applicationDirsMock->expects($this->any())
            ->method('getDir')
            ->will($this->returnValue(__DIR__ . '/_files/design'));
        $widgetConfigs = $this->_object->get('widget.xml', 'design');
        $expected = realpath(__DIR__ . '/_files/design/frontend/Test/etc/widget.xml');
        $this->assertCount(1, $widgetConfigs);
        $this->assertEquals($expected, realpath($widgetConfigs[0]));
    }

    public function testGetGlobal()
    {
        $this->_applicationDirsMock->expects($this->any())
            ->method('getDir')
            ->will($this->returnValue(__DIR__ . '/_files/code'));
        $widgetConfigs = $this->_object->get('widget.xml', 'global');
        $expected = realpath(__DIR__ . '/_files/code/Magento/Test/etc/widget.xml');
        $this->assertCount(1, $widgetConfigs);
        $this->assertEquals($expected, realpath($widgetConfigs[0]));
    }
}
