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
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Tax\Model\Calculation\RateFactory
     */
    protected $taxRateFactory;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->taxRateFactory = $this->objectManager->create('Magento\Tax\Model\Calculation\RateFactory');
    }

    /**
     * @param array $data
     * @dataProvider createTaxRateDataObjectFromModelDataProvider
     */
    public function testCreateTaxRateDataObjectFromModel($data)
    {
        $taxRateModel = $this->taxRateFactory->create(['data' => $data]);

        $taxRateDataOjectBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxRateBuilder');
        $zipRangeDataObjectBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\ZipRangeBuilder');
        /** @var  $converter \Magento\Tax\Model\Calculation\Rate\Converter */
        $converter = $this->objectManager->create(
            'Magento\Tax\Model\Calculation\Rate\Converter',
            [
                'taxRateDataObjectBuilder' => $taxRateDataOjectBuilder,
                'zipRangeDataObjectBuilder' => $zipRangeDataObjectBuilder,
            ]
        );
        $taxRateDataObject = $converter->createTaxRateDataObjectFromModel($taxRateModel);
        $this->assertEquals($taxRateModel->getId(), $taxRateDataObject->getId());
        $this->assertEquals($taxRateModel->getTaxCountryId(), $taxRateDataObject->getCountryId());
        $this->assertEquals($taxRateModel->getTaxRegionId(), $taxRateDataObject->getRegionId());
        $this->assertEquals($taxRateModel->getTaxPostcode(), $taxRateDataObject->getPostcode());
        $this->assertEquals($taxRateModel->getCode(), $taxRateDataObject->getcode());
        $this->assertEquals($taxRateModel->getRate(), $taxRateDataObject->getPercentageRate());
        $zipIsRange = $taxRateModel->getZipIsRange();
        if ($zipIsRange) {
            $this->assertEquals(
                $taxRateModel->getZipFrom(),
                $taxRateDataObject->getZipRange()->getFrom()
            );
            $this->assertEquals(
                $taxRateModel->getZipTo(),
                $taxRateDataObject->getZipRange()->getTo()
            );
        } else {
            $this->assertNull($taxRateDataObject->getZipRange());
        }
    }

    public function createTaxRateDataObjectFromModelDataProvider()
    {
        return [
            [
                [
                    'id' => '1',
                    'countryId' => 'US',
                    'regionId' => '34',
                    'code' => 'US-CA-*-Rate 1',
                    'rate' => '8.25',
                    'zipIsRange' => '1',
                    'zipFrom' => '78701',
                    'zipTo' => '78759',
                ],
            ],
            [
                [
                    'id' => '1',
                    'countryId' => 'US',
                    'code' => 'US-CA-*-Rate 1',
                    'rate' => '8.25',
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
        $taxRateDataObjectBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\TaxRateBuilder');
        $zipRangeDataObjectBuilder = $this->objectManager->create('Magento\Tax\Service\V1\Data\ZipRangeBuilder');

        /** @var  $converter \Magento\Tax\Model\Calculation\Rate\Converter */
        $converter = $this->objectManager->create(
            'Magento\Tax\Model\Calculation\Rate\Converter',
            [
                'taxRateDataObjectBuilder' => $taxRateDataObjectBuilder,
                'zipRangeDataObjectBuilder' => $zipRangeDataObjectBuilder,
            ]
        );

        $taxRateDataObject = $taxRateDataObjectBuilder->populateWithArray($data)->create();
        $taxRateModel = $converter->createTaxRateModel($taxRateDataObject);

        //Assertion
        $this->assertEquals($taxRateDataObject->getId(), $taxRateModel->getId());
        $this->assertEquals($taxRateDataObject->getCountryId(), $taxRateModel->getTaxCountryId());
        $this->assertEquals($taxRateDataObject->getRegionId(), $taxRateModel->getTaxRegionId());
        $this->assertEquals($taxRateDataObject->getPostcode(), $taxRateModel->getTaxPostcode());
        $this->assertEquals($taxRateDataObject->getcode(), $taxRateModel->getCode());
        $this->assertEquals($taxRateDataObject->getPercentageRate(), $taxRateModel->getRate());
        if ($taxRateDataObject->getZipRange()) {
            if ($taxRateDataObject->getZipRange()->getFrom()) {
                $this->assertEquals(
                    $taxRateDataObject->getZipRange()->getFrom(),
                    $taxRateModel->getZipFrom()
                );
            } else {
                $this->assertNull($taxRateModel->getZipFrom());
            }
            if ($taxRateDataObject->getZipRange()->getTo()) {
                $this->assertEquals(
                    $taxRateDataObject->getZipRange()->getTo(),
                    $taxRateModel->getZipTo()
                );
            } else {
                $this->assertNull($taxRateModel->getTo());
            }
        } else {
            $this->assertNull($taxRateModel->getZipFrom());
            $this->assertNull($taxRateModel->getZipTo());
        }
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
}
