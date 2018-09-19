<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

use \Magento\Setup\Module\Di\Code\Scanner\ServiceDataAttributesScanner;

class ServiceDataAttributesScannerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Scanner\ServiceDataAttributesScanner
     */
    protected $model;

    /**
     * @var string
     */
    protected $testFile;

    protected function setUp()
    {
        $this->model = new ServiceDataAttributesScanner();
        $this->testFile = str_replace('\\', '/', realpath(__DIR__ . '/../../') . '/_files/extension_attributes.xml');
    }

    public function testCollectEntities()
    {
        $files = [$this->testFile];
        $expectedResult = [
            \Magento\Sales\Api\Data\OrderExtensionInterface::class,
            \Magento\Sales\Api\Data\OrderExtension::class,
            \Magento\Sales\Api\Data\OrderItemExtensionInterface::class,
            \Magento\Sales\Api\Data\OrderItemExtension::class,
        ];
        $this->assertSame($expectedResult, $this->model->collectEntities($files));
    }
}
