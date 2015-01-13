<?php
/**
 * Test services for name collisions.
 *
 * Let we have two service interfaces called Foo\Bar\Service\SomeBazV1Interface and Foo\Bar\Service\Some\BazV1Interface.
 * Given current name generation logic both are going to be translated to BarSomeBazV1. This test checks such things
 * are not going to happen.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi;

class ServiceNameCollisionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test there are no collisions between service names.
     *
     * @see \Magento\Webapi\Helper\Data::getServiceName()
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testServiceNameCollisions()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Webapi\Helper\Data $helper */
        $helper = $objectManager->get('Magento\Webapi\Helper\Data');
        /** @var \Magento\Webapi\Model\Config $webapiConfig */
        $webapiConfig = $objectManager->get('Magento\Webapi\Model\Config');
        $serviceNames = [];

        foreach (array_keys($webapiConfig->getServices()['services']) as $serviceClassName) {
            $newServiceName = $helper->getServiceName($serviceClassName);
            $this->assertFalse(in_array($newServiceName, $serviceNames));
            $serviceNames[] = $newServiceName;
        }
    }
}
