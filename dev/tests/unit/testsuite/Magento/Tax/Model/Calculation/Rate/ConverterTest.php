<?php
/**
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
namespace Magento\Tax\Model\Calculation\Rate;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    /**
     * @param array $valueMap
     * @dataProvider createTaxRateDataObjectFromModelDataProvider
     */
    public function testCreateTaxRateDataObjectFromModel($valueMap)
    {
        $taxRateModelMock = $this->getMockBuilder(
            'Magento\Tax\Model\Calculation\Rate'
        )->disableOriginalConstructor()->setMethods(
            [
                'getId',
                'getTaxCountryId',
                'getTaxRegionId',
                'getTaxPostcode',
                'getCode',
                'getRate',
                'getZipIsRange',
                'getZipFrom',
                'getZipTo',
                'getTitles',
                '__wakeup',
            ]
        )->getMock();
        $this->mockReturnValue($taxRateModelMock, $valueMap);

        $taxRateDataOjectBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxRateBuilder');
        $zipRangeDataObjectBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\ZipRangeBuilder');

        $directoryRegionModel = $this->getMockBuilder('Magento\Directory\Model\Region')
            ->disableOriginalConstructor()
            ->setMethods(['load', 'getCode', '__wakeup'])
            ->getMock();
        if ($this->getExpectedValue($valueMap, 'getTaxRegionId') !== null) {
            $directoryRegionModel->expects($this->once())
                ->method('load')
                ->with($this->getExpectedValue($valueMap, 'getTaxRegionId'))
                ->will($this->returnSelf());
            $codeForRegion = 'Antarctica';
            $directoryRegionModel->expects($this->once())
                ->method('getCode')
                ->will($this->returnValue($codeForRegion));
        }

        $taxRateTitleDataObjectBuilder = $this->objectManager->getObject(
            'Magento\Tax\Service\V1\Data\TaxRateTitleBuilder'
        );
        /** @var  $converter \Magento\Tax\Model\Calculation\Rate\Converter */
        $converter = $this->objectManager->getObject(
            'Magento\Tax\Model\Calculation\Rate\Converter',
            [
                'taxRateDataObjectBuilder' => $taxRateDataOjectBuilder,
                'zipRangeDataObjectBuilder' => $zipRangeDataObjectBuilder,
                'taxRateTitleDataObjectBuilder' => $taxRateTitleDataObjectBuilder,
                'directoryRegion' => $directoryRegionModel,
            ]
        );
        $taxRateDataObject = $converter->createTaxRateDataObjectFromModel($taxRateModelMock);
        $this->assertEquals($this->getExpectedValue($valueMap, 'getId'), $taxRateDataObject->getId());
        $this->assertEquals($this->getExpectedValue($valueMap, 'getTaxCountryId'), $taxRateDataObject->getCountryId());
        $this->assertEquals($this->getExpectedValue($valueMap, 'getTaxRegionId'), $taxRateDataObject->getRegionId());
        ///* make sure that 0 is an acceptable value and is converted */
        $this->assertTrue($this->getExpectedValue($valueMap, 'getTaxRegionId') === $taxRateDataObject->getRegionId());
        if ($this->getExpectedValue($valueMap, 'getTaxRegionId') !== null) {
            $this->assertEquals($codeForRegion, $taxRateDataObject->getRegionName());
        }
        $this->assertEquals($this->getExpectedValue($valueMap, 'getTaxPostcode'), $taxRateDataObject->getPostcode());
        $this->assertEquals($this->getExpectedValue($valueMap, 'getCode'), $taxRateDataObject->getCode());
        $this->assertEquals($this->getExpectedValue($valueMap, 'getRate'), $taxRateDataObject->getPercentageRate());
        $zipIsRange = $this->getExpectedValue($valueMap, 'getZipIsRange');
        if ($zipIsRange) {
            $this->assertEquals(
                $this->getExpectedValue($valueMap, 'getZipFrom'),
                $taxRateDataObject->getZipRange()->getFrom()
            );
            $this->assertEquals(
                $this->getExpectedValue($valueMap, 'getZipTo'),
                $taxRateDataObject->getZipRange()->getTo()
            );
        } else {
            $this->assertNull($taxRateDataObject->getZipRange());
        }

        $titles = $this->getExpectedValue($valueMap, 'getTitles');
        if ($titles) {
            $dataObjectTitles = $taxRateDataObject->getTitles();
            foreach ($titles as $title) {
                $found = false;
                foreach ($dataObjectTitles as $dataObjectTitle) {
                    if (($dataObjectTitle->getValue() === $title->getValue())
                        && $dataObjectTitle->getStoreId() === $title->getStoreId()) {
                        $found = true;
                    }
                }
                $this->assertTrue($found, "Did not find title for " . $title->getValue());
            }
        }
        $this->assertEquals(count($titles), count($taxRateDataObject->getTitles()));
    }

    public function createTaxRateDataObjectFromModelDataProvider()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $titleModel1 = $this->objectManager->getObject('Magento\Tax\Model\Calculation\Rate\Title');
        $titleModel1->setValue('tax title');
        $titleModel1->setStoreId(5);

        $titleModel2 = $this->objectManager->getObject('Magento\Tax\Model\Calculation\Rate\Title');
        $titleModel2->setValue('tax title 2');
        $titleModel2->setStoreId(1);

        return [
            [
                [
                    'getId' => '1',
                    'getTaxCountryId' => 'US',
                    'getTaxRegionId' => '34',
                    'getCode' => 'US-CA-*-Rate 1',
                    'getRate' => '8.25',
                    'getZipIsRange' => '1',
                    'getZipFrom' => '78701',
                    'getZipTo' => '78759',
                ],
            ],
            [
                [
                    'getId' => '1',
                    'getTaxCountryId' => 'US',
                    'getCode' => 'US-CA-*-Rate 1',
                    'getRate' => '8.25',
                ],
            ],
            [
                [
                    'getId' => '1',
                    'getTaxCountryId' => 'US',
                    // explicitly 0 to make sure region id is set
                    'getTaxRegionId' => 0,
                    'getCode' => 'US-CA-*-Rate 1',
                    'getRate' => '8.25',
                ],
            ],
            [
                [
                    'getId' => '1',
                    'getTaxCountryId' => 'US',
                    'getTaxRegionId' => '34',
                    'getCode' => 'US-CA-*-Rate 1',
                    'getRate' => '8.25',
                    'getTitles' => [
                        $titleModel1
                    ]
                ],
            ],
        ];
    }

    /**
     * @param array $data
     * @dataProvider createTaxRateModelDataProvider
     */
    public function testCreateTaxRateModel($data)
    {
        $zipRangeBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\ZipRangeBuilder');
        $taxRateBuilder = $this->objectManager->getObject(
            'Magento\Tax\Service\V1\Data\TaxRateBuilder',
            ['zipRangeBuilder' => $zipRangeBuilder]
        );
        /** @var  $taxRateDataObject \Magento\Tax\Service\V1\Data\TaxRate */
        $taxRateDataObject = $taxRateBuilder->populateWithArray($data)->create();

        $rateModelMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\Rate')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setId',
                    'setTaxCountryId',
                    'setTaxRegionId',
                    'setTaxPostcode',
                    'setRate',
                    'setZipFrom',
                    'setZipTo',
                    '__wakeup',
                ]
            )
            ->getMock();

        $rateModelMock->expects($this->once())
            ->method('setId')
            ->with($taxRateDataObject->getId());
        $rateModelMock->expects($this->once())
            ->method('setTaxCountryId')
            ->with($taxRateDataObject->getCountryId());
        $rateModelMock->expects($this->once())
            ->method('setTaxRegionId')
            ->with($taxRateDataObject->getRegionId());
        $rateModelMock->expects($this->once())
            ->method('setTaxPostcode')
            ->with($taxRateDataObject->getPostcode());
        $rateModelMock->expects($this->once())
            ->method('setRate')
            ->with($taxRateDataObject->getPercentageRate());
        if ($taxRateDataObject->getZipRange()) {
            $rateModelMock->expects($this->once())
                ->method('setZipFrom')
                ->with($taxRateDataObject->getZipRange()->getFrom());
            $rateModelMock->expects($this->once())
                ->method('setZipTo')
                ->with($taxRateDataObject->getZipRange()->getTo());
        }

        $rateModelFactoryMock = $this->getMockBuilder('Magento\Tax\Model\Calculation\RateFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $rateModelFactoryMock->expects($this->once())->method('create')->will($this->returnValue($rateModelMock));

        /** @var  $converter \Magento\Tax\Model\Calculation\Rate\Converter */
        $converter = $this->objectManager->getObject(
            'Magento\Tax\Model\Calculation\Rate\Converter',
            [
                'taxRateDataObjectBuilder' => $taxRateBuilder,
                'taxRateModelFactory' => $rateModelFactoryMock,
                'zipRangeDataObjectBuilder' => $zipRangeBuilder
            ]
        );

        $taxRateModel = $converter->createTaxRateModel($taxRateDataObject);

        $this->assertSame($rateModelMock, $taxRateModel);
    }

    public function createTaxRateModelDataProvider()
    {
        return [
            'withZipRange' => [
                [
                    'id' => '1',
                    'country_id' => 'US',
                    'region_id' => '34',
                    'code' => 'US-CA-*-Rate 2',
                    'percentage_rate' => '8.25',
                    'zip_range' => ['from' => 78765, 'to' => 78780]
                ],
            ],
            'withZipRangeFrom' => [
                [
                    'id' => '1',
                    'country_id' => 'US',
                    'region_id' => '34',
                    'code' => 'US-CA-*-Rate 2',
                    'percentage_rate' => '8.25',
                    'zip_range' => ['from' => 78765]
                ],
            ],
            'withZipRangeTo' => [
                [
                    'id' => '1',
                    'country_id' => 'US',
                    'region_id' => '34',
                    'code' => 'US-CA-*-Rate 2',
                    'percentage_rate' => '8.25',
                    'zip_range' => ['to' => 78780]
                ],
            ],
            'withPostalCode' => [
                [
                    'id' => '1',
                    'country_id' => 'US',
                    'code' => 'US-CA-*-Rate 1',
                    'rate' => '8.25',
                    'postcode' => '78727'
                ],
            ]
        ];
    }

    private function getExpectedValue($valueMap, $key)
    {
        return array_key_exists($key, $valueMap) ? $valueMap[$key] : null;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $valueMap
     */
    private function mockReturnValue(\PHPUnit_Framework_MockObject_MockObject $mock, $valueMap)
    {
        foreach ($valueMap as $method => $value) {
            $mock->expects($this->any())->method($method)->will($this->returnValue($value));
        }
    }

    /**
     * @param array $data
     * @dataProvider createTaxRateTitleDataProvider
     */
    public function testCreateTitlesFromServiceObject($data)
    {
        $taxRateBuilder = $this->objectManager->getObject(
            'Magento\Tax\Service\V1\Data\TaxRateBuilder'
        );

        $taxRate = $taxRateBuilder->setTitles($data)->create();

        /** @var  $converter \Magento\Tax\Model\Calculation\Rate\Converter */
        $converter = $this->objectManager->getObject(
            'Magento\Tax\Model\Calculation\Rate\Converter'
        );

        $titles = $converter->createTitleArrayFromServiceObject($taxRate);
        foreach ($data as $expectedTitle) {
            $storeId = $expectedTitle->getStoreId();
            $this->assertTrue(isset($titles[$storeId]), "Title for store id {$storeId} was not set.");
            $this->assertEquals($expectedTitle->getValue(), $titles[$storeId]);
        }
    }

    public function createTaxRateTitleDataProvider()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $titleBuilder = $this->objectManager->getObject('Magento\Tax\Service\V1\Data\TaxRateTitleBuilder');
        $titleBuilder->setValue('tax title');
        $titleBuilder->setStoreId(5);

        $title1 = $titleBuilder->create();

        $titleBuilder->setValue('tax title 2');
        $titleBuilder->setStoreId(1);

        $title2 = $titleBuilder->create();

        return [
            'no titles' => [
                []
            ],
            '1 title' => [
                [
                    $title1
                ]
            ],
            '2 title2' => [
                [
                  $title1,
                  $title2,
                ]
            ]
        ];
    }
}
