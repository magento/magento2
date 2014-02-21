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
 * @package     Magento_PageCache
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\PageCache\Model;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\PageCache\Model\Config  */
    protected $_model;

    /**
     * setUp all mocks and data function
     */
    public function setUp()
    {
        $filesystemMock = $this->getMock('Magento\App\Filesystem', ['getDirectoryRead'], [], '', false);
        $coreStoreConfigMock = $this->getMock('Magento\Core\Model\Store\Config', ['getConfig'], [], '', false);
        $appConfigMock = $this->getMockForAbstractClass(
            'Magento\App\ConfigInterface',
            [],
            '',
            true,
            true,
            true,
            ['getValue']
        );

        $modulesDirectoryMock = $this->getMock('Magento\Filesystem\Directory\Write', [], [], '', false);
        $filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(\Magento\App\Filesystem::MODULES_DIR)
            ->will($this->returnValue($modulesDirectoryMock));
        $modulesDirectoryMock->expects($this->once())
            ->method('readFile')
            ->will($this->returnValue(file_get_contents(__DIR__ . '/_files/test.vcl')));
        $coreStoreConfigMock->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValueMap([
                [\Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_BACKEND_HOST, null, 'example.com'],
                [\Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_BACKEND_PORT, null, '8080'],
                [\Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_ACCESS_LIST, null, '127.0.0.1, 192.168.0.1'],
                [
                    \Magento\PageCache\Model\Config::XML_VARNISH_PAGECACHE_DESIGN_THEME_REGEX,
                    null,
                    serialize([
                        [
                            'regexp' => '(?i)pattern',
                            'value'  => 'value_for_pattern'
                        ]
                    ])
                ]
            ]));

        $this->_model = new \Magento\PageCache\Model\Config($filesystemMock, $coreStoreConfigMock, $appConfigMock);
    }

    /**
     * test for getVcl method
     */
    public function testGetVcl()
    {
        $test = $this->_model->getVclFile();
        $this->assertEquals(file_get_contents(__DIR__ . '/_files/result.vcl'), $test);
    }
}
