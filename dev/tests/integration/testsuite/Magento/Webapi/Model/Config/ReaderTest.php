<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Webapi\Model\Config;

use Magento\Webapi\Model\Config\Reader as ConfigReader;

/**
 * Webapi config reader test.
 */
class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $_fileResolverMock;

    /** @var ConfigReader */
    protected $_configReader;

    protected function setUp()
    {
        parent::setUp();
        $this->_fileResolverMock = $this->createMock(\Magento\Framework\Config\FileResolverInterface::class);
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_configReader = $objectManager->create(
            \Magento\Webapi\Model\Config\Reader::class,
            ['fileResolver' => $this->_fileResolverMock]
        );
    }

    public function testRead()
    {
        $configFiles = [
            file_get_contents(realpath(__DIR__ . '/_files/webapiA.xml')),
            file_get_contents(realpath(__DIR__ . '/_files/webapiB.xml')),
        ];
        $this->_fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($configFiles));

        $expectedResult = require __DIR__ . '/_files/webapi.php';
        $this->assertEquals($expectedResult, $this->_configReader->read(), 'Error happened during config reading.');
    }
}
