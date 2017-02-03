<?php
/**
 * Test services for name collisions.
 *
 * Let we have two service interfaces called Foo\Bar\Service\SomeBazV1Interface and Foo\Bar\Service\Some\BazV1Interface.
 * Given current name generation logic both are going to be translated to BarSomeBazV1. This test checks such things
 * are not going to happen.
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi;

use Magento\Webapi\Model\Config\Converter;

class ServiceNameCollisionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test there are no collisions between service names.
     *
     * @see \Magento\Webapi\Model\Soap\Config::getServiceName()
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testServiceNameCollisions()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Webapi\Model\ServiceMetadata $serviceMetadata */
        $serviceMetadata = $objectManager->get('Magento\Webapi\Model\ServiceMetadata');
        /** @var \Magento\Webapi\Model\Config $webapiConfig */
        $webapiConfig = $objectManager->get('Magento\Webapi\Model\Config');
        $serviceNames = [];

        foreach ($webapiConfig->getServices()[Converter::KEY_SERVICES] as $serviceClassName => $serviceVersionData) {
            foreach ($serviceVersionData as $version => $serviceData) {
                $newServiceName = $serviceMetadata->getServiceName($serviceClassName, $version);
                $this->assertFalse(in_array($newServiceName, $serviceNames));
                $serviceNames[] = $newServiceName;
            }
        }
    }
}
