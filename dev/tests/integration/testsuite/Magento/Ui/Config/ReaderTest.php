<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Config;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Ui\Config\FileResolverStub;

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Reader
     */
    private $reader;

    public function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();

        $objectManager->configure(
            [
                'preferences' => [
                    \Magento\Ui\Config\Reader\FileResolver::class => FileResolverStub::class
                ]
            ]
        );

        $this->reader = $objectManager->create(
            Reader::class,
            [
                'fileName' => 'test_component.xml'
            ]
        );
    }

    public function tearDown()
    {
        $objectManager = Bootstrap::getObjectManager();

        $objectManager->configure(
            [
                'preferences' => [
                    \Magento\Ui\Config\Reader\FileResolver::class => \Magento\Ui\Config\Reader\FileResolver::class
                ]
            ]
        );

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testReader()
    {
        $mergedConfiguration = include __DIR__ . '/../_files/expected_result_configuration.php';
        $readConfiguration = $this->reader->read();

        $this->assertEquals($mergedConfiguration, $readConfiguration);
    }
}
