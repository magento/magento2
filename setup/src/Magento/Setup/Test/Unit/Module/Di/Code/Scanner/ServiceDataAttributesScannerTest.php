<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Module\Di\Code\Scanner;

use \Magento\Setup\Module\Di\Code\Scanner\ServiceDataAttributesScanner;

class ServiceDataAttributesScannerTest extends \PHPUnit_Framework_TestCase
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
            'Magento\Sales\Api\Data\OrderExtensionInterface',
            'Magento\Sales\Api\Data\OrderExtension',
            'Magento\Sales\Api\Data\OrderItemExtensionInterface',
            'Magento\Sales\Api\Data\OrderItemExtension',
        ];
        $this->assertSame($expectedResult, $this->model->collectEntities($files));
    }
}
