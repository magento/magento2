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
 *
 */
namespace Magento\Webapi\Model\Config\Integration;

use Magento\Webapi\Model\Config\Integration\Reader as ConfigReader;

/**
 * Integration API config reader test.
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_fileResolverMock;

    /** @var ConfigReader */
    protected $_configReader;

    protected function setUp()
    {
        parent::setUp();
        $this->_fileResolverMock = $this->getMock('Magento\Framework\Config\FileResolverInterface');
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_configReader = $objectManager->create(
            'Magento\Webapi\Model\Config\Integration\Reader',
            array('fileResolver' => $this->_fileResolverMock)
        );
    }

    public function testRead()
    {
        $configFiles = array(
            file_get_contents(realpath(__DIR__ . '/_files/apiA.xml')),
            file_get_contents(realpath(__DIR__ . '/_files/apiB.xml'))
        );
        $this->_fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($configFiles));

        $expectedResult = require __DIR__ . '/_files/api.php';
        $this->assertEquals($expectedResult, $this->_configReader->read(), 'Error happened during config reading.');
    }
}
