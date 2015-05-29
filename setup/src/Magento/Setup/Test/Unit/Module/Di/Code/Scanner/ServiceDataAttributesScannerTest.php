<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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

    protected function setUp()
    {
        $this->model = new ServiceDataAttributesScanner();
    }

    public function testCollectEntities()
    {
        $files = [realpath('../../_files/service_data_attributes.xml')];
        $expectedResult = [
            'Magento\Sales\Api\Data\OrderExtensionInterface',
            'Magento\Sales\Api\Data\OrderExtension',
            'Magento\Sales\Api\Data\OrderItemExtensionInterface',
            'Magento\Sales\Api\Data\OrderItemExtension',
            'Magento\GiftMessage\Api\Data\MessageExtensionInterface',
            'Magento\GiftMessage\Api\Data\MessageExtension',
            'Magento\Quote\Api\Data\TotalsAdditionalDataExtensionInterface',
            'Magento\Quote\Api\Data\TotalsAdditionalDataExtension'
        ];

        $this->assertEquals($expectedResult, $this->model->collectEntities($files));
    }
}
