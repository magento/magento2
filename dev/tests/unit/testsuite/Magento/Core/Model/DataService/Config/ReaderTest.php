<?php
/**
 * \Magento\Core\Model\DataService\Config\Reader
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
namespace Magento\Core\Model\DataService\Config;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Core\Model\DataService\Config\Reader */
    private $_configReader;

    /** @var \PHPUnit_Framework_MockObject_MockObject  */
    private $_modulesReaderMock;

    /**
     * Prepare object manager with mocks of objects required by config reader.
     */
    protected function setUp()
    {
        $path = array(__DIR__, '..', '_files', 'service_calls.xml');
        $path = realpath(implode('/', $path));
        $this->_modulesReaderMock = $this->getMockBuilder('Magento\Core\Model\Config\Modules\Reader')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_configReader = new \Magento\Core\Model\DataService\Config\Reader(
            $this->_modulesReaderMock,
            array($path)
        );
    }

    /**
     * Verify correct schema file is returned.
     */
    public function testGetSchemaFile()
    {
        $etcDir = 'app/code/Magento/Core/etc';
        $expectedPath = $etcDir . '/service_calls.xsd';
        $this->_modulesReaderMock->expects($this->any())->method('getModuleDir')
            ->with('etc', 'Magento_Core')
            ->will($this->returnValue($etcDir));
        $result = $this->_configReader->getSchemaFile();
        $this->assertNotNull($result);
        $this->assertEquals($expectedPath, $result, 'returned schema file is wrong');
    }
}
