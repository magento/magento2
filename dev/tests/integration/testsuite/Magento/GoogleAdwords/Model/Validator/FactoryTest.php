<?php
/**
 * Integration test for \Magento\GoogleAdwords\Model\Validator\Factory
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Model\Validator;

use Magento\TestFramework\Helper\Bootstrap;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test creation of conversion id validator
     *
     * @magentoAppIsolation enabled
     */
    public function testGetConversionIdValidator()
    {
        $conversionId = '123';

        $objectManager = Bootstrap::getObjectManager();
        $factory = $objectManager->get(\Magento\GoogleAdwords\Model\Validator\Factory::class);

        $validator = $factory->createConversionIdValidator($conversionId);
        $this->assertNotNull($validator, "Conversion ID Validator");
    }

    /**
     * Test creation of conversion color validator
     *
     * @magentoAppIsolation enabled
     */
    public function testGetConversionColorValidator()
    {
        $conversionColor = "FFFFFF";

        $objectManager = Bootstrap::getObjectManager();
        $factory = $objectManager->get(\Magento\GoogleAdwords\Model\Validator\Factory::class);

        $validator = $factory->createColorValidator($conversionColor);
        $this->assertNotNull($validator);
    }
}
