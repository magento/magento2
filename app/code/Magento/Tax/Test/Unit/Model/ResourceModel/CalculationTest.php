<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Tax\Test\Unit\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Helper\Data;
use Magento\Tax\Model\ResourceModel\Calculation;
use PHPUnit\Framework\TestCase;

class CalculationTest extends TestCase
{
    /**
     * Tests the building of the search templates for the postal code
     *
     * @param string $postalCode
     * @param string|null $exactPostalcode
     * @dataProvider dataProviderCreateSearchPostCodeTemplates
     */
    public function testCreateSearchPostCodeTemplates($postalCode, $exactPostalcode)
    {
        // create the mocks
        $resource = $this->createMock(ResourceConnection::class);
        $storeManager = $this->getMockForAbstractClass(StoreManagerInterface::class);

        $taxData = $this->createPartialMock(Data::class, ['getPostCodeSubStringLength']);
        $taxData
            ->expects($this->any())
            ->method('getPostCodeSubStringLength')
            ->willReturn(10);

        $objectManager = new ObjectManager($this);
        $calcMock = $objectManager->getObject(
            Calculation::class,
            [
                'resource' => $resource,
                'taxData' => $taxData,
                'storeManager' => $storeManager
            ]
        );

        // get access to the method
        $method = new \ReflectionMethod(
            Calculation::class,
            '_createSearchPostCodeTemplates'
        );
        $method->setAccessible(true);

        // test & verify
        $resultsArr = $method->invokeArgs($calcMock, [$postalCode, $exactPostalcode]);
        $this->verifyResults($resultsArr, $postalCode, $exactPostalcode);
    }

    /**
     * Verify the results array, based on certain properties of the codes
     *
     * @param array $resultsArr
     * @param string $code1
     * @param string|null $code2
     */
    private function verifyResults($resultsArr, $code1, $code2 = null)
    {
        // determine expected size of the results array
        $expectedSize = strlen($code1) + 1; // array will also include the vanilla 'code1' value
        if ($code2) {
            $expectedSize = strlen($code2) + 2; // array will include both 'code1' and 'code2'
        }
        $actualSize = count($resultsArr);
        $this->assertEquals(
            $expectedSize,
            $actualSize,
            'Expected size of the result array was ' . $expectedSize . ' but actual was ' . $actualSize
        );

        // verify code(s) are present within the array
        $this->assertTrue(in_array($code1, $resultsArr), 'Expected to find code "' . $code1 . '"');
        if ($code2) {
            $this->assertTrue(in_array($code2, $resultsArr), 'Expected to find code "' . $code2 . '"');
        }
    }

    /**
     * @return array
     */
    public static function dataProviderCreateSearchPostCodeTemplates()
    {
        return [
            'USA basic' => ['78729', null],
            'USA zip+4' => ['54321', '12345-6789'],
            'Poland' => ['05-509', null]
        ];
    }
}
