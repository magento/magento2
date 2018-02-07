<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
namespace Magento\Integration\Model\Config\Consolidated;

use Magento\Integration\Model\Config\Consolidated\Reader as ConfigReader;

/**
 * Integration config reader test.
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $fileResolverMock;

    /** @var ConfigReader */
    protected $configReader;

    protected function setUp()
    {
        parent::setUp();
        $this->fileResolverMock = $this->getMockBuilder('Magento\Framework\Config\FileResolverInterface')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->configReader = $objectManager->create(
            'Magento\Integration\Model\Config\Consolidated\Reader',
            ['fileResolver' => $this->fileResolverMock]
        );
    }

    public function testRead()
    {
        $configFiles = [
            file_get_contents(realpath(__DIR__ . '/_files/integrationA.xml')),
            file_get_contents(realpath(__DIR__ . '/_files/integrationB.xml'))
        ];
        $this->fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($configFiles));

        $expectedResult = require __DIR__ . '/_files/integration.php';
        $this->assertEquals($expectedResult, $this->configReader->read(), 'Error happened during config reading.');
    }
}
