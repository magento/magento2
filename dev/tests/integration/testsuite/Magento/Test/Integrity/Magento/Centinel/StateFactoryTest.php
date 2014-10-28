<?php
/**
 * Verifies that the card types defined in payment xml matches the types declared in factory via DI.
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
namespace Magento\Test\Integrity\Magento\Centinel;

class StateFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryTypes()
    {
        $factoryTypes = $this->_getFactoryTypes();
        $ccTypes = $this->_getCcTypes();

        $definedTypes = array_intersect($factoryTypes, $ccTypes);

        $this->assertEquals(
            $factoryTypes,
            $definedTypes,
            'Some factory types are missing from payments config.' . "\nMissing types: " . implode(
                ',',
                array_diff($factoryTypes, $definedTypes)
            )
        );
    }

    /**
     * Get factory, find list of types it has
     *
     * @return array string[] factoryTypes
     */
    private function _getFactoryTypes()
    {
        /** @var \Magento\Centinel\Model\StateFactory $stateFactory */
        $stateFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Centinel\Model\StateFactory'
        );
        $reflectionObj = new \ReflectionClass($stateFactory);
        $stateMapProp = $reflectionObj->getProperty('_stateClassMap');
        $stateMapProp->setAccessible(true);
        $stateClassMap = $stateMapProp->getValue($stateFactory);
        $factoryTypes = array_keys($stateClassMap);
        return $factoryTypes;
    }

    /**
     * Get config, find list of types it has
     *
     * @return array string[] ccTypes
     */
    private function _getCcTypes()
    {
        /** @var \Magento\Payment\Model\Config $paymentConfig */
        $paymentConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Payment\Model\Config'
        );
        $ccTypes = array_keys($paymentConfig->getCcTypes());
        return $ccTypes;
    }
}
