<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Abstract test case to test positions of a module's total collectors as compared to other collectors
 */
namespace Magento\Sales\Model;

abstract class AbstractCollectorPositionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $collectorCode
     * @param string $configType
     * @param array $before
     * @param array $after
     *
     * @dataProvider collectorPositionDataProvider
     */
    public function testCollectorPosition($collectorCode, $configType, array $before, array $after)
    {
        $allCollectors = self::_getConfigCollectors($configType);
        $collectorCodes = array_keys($allCollectors);
        $collectorPos = array_search($collectorCode, $collectorCodes);
        $this->assertNotSame(false, $collectorPos, "'{$collectorCode}' total collector is not found");

        foreach ($before as $compareWithCode) {
            $compareWithPos = array_search($compareWithCode, $collectorCodes);
            if ($compareWithPos === false) {
                continue;
            }
            $this->assertLessThan(
                $compareWithPos,
                $collectorPos,
                "The '{$collectorCode}' collector must go before '{$compareWithCode}'"
            );
        }

        foreach ($after as $compareWithCode) {
            $compareWithPos = array_search($compareWithCode, $collectorCodes);
            if ($compareWithPos === false) {
                continue;
            }
            $this->assertGreaterThan(
                $compareWithPos,
                $collectorPos,
                "The '{$collectorCode}' collector must go after '{$compareWithCode}'"
            );
        }
    }

    /**
     * Return array of total collectors for the designated $configType
     *
     * @var string $configType
     * @throws \InvalidArgumentException
     * @return array
     */
    protected static function _getConfigCollectors($configType)
    {
        switch ($configType) {
            case 'quote':
                $configClass = 'Magento\Quote\Model\Quote\Address\Total\Collector';
                $methodGetCollectors = 'getCollectors';
                break;
            case 'invoice':
                $configClass = 'Magento\Sales\Model\Order\Invoice\Config';
                $methodGetCollectors = 'getTotalModels';
                break;
            case 'creditmemo':
                $configClass = 'Magento\Sales\Model\Order\Creditmemo\Config';
                $methodGetCollectors = 'getTotalModels';
                break;
            default:
                throw new \InvalidArgumentException('Unknown config type: ' . $configType);
        }
        $config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create($configClass);
        return $config->{$methodGetCollectors}();
    }

    /**
     * Data provider with the data to verify
     *
     * @return array
     */
    abstract public function collectorPositionDataProvider();
}
