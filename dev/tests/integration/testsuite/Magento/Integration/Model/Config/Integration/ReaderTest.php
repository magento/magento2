<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Integration\Model\Config\Integration;

use Magento\Integration\Model\Config\Integration\Reader as ConfigReader;

/**
 * Integration API config reader test.
 */
class ReaderTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $_fileResolverMock;

    /** @var ConfigReader */
    protected $_configReader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->_fileResolverMock = $this->createMock(\Magento\Framework\Config\FileResolverInterface::class);
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_configReader = $objectManager->create(
            \Magento\Integration\Model\Config\Integration\Reader::class,
            ['fileResolver' => $this->_fileResolverMock]
        );
    }

    public function testRead()
    {
        $configFiles = [
            file_get_contents(realpath(__DIR__ . '/_files/apiA.xml')),
            file_get_contents(realpath(__DIR__ . '/_files/apiB.xml')),
        ];
        $this->_fileResolverMock->expects($this->any())->method('get')->willReturn($configFiles);

        $expectedResult = require __DIR__ . '/_files/api.php';
        $this->assertEquals($expectedResult, $this->_configReader->read(), 'Error happened during config reading.');
    }
}
