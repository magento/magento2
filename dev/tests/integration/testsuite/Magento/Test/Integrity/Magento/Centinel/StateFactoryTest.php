<?php
/**
 * Verifies that the card types defined in payment xml matches the types declared in factory via DI.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
