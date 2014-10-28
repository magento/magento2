<?php
/**
 * Test services for name collisions.
 *
 * Let we have two service interfaces called Foo\Bar\Service\SomeBazV1Interface and Foo\Bar\Service\Some\BazV1Interface.
 * Given current name generation logic both are going to be translated to BarSomeBazV1. This test checks such things
 * are not going to happen.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $serviceNames = array();

        foreach (array_keys($webapiConfig->getServices()['services']) as $serviceClassName) {
            $newServiceName = $helper->getServiceName($serviceClassName);
            $this->assertFalse(in_array($newServiceName, $serviceNames));
            $serviceNames[] = $newServiceName;
        }
    }
}
