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
namespace Magento\Tax\Model\Calculation;

class RateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     *  Init data
     */
    public function setUp()
    {
        $this->objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->resourceMock = $this->getMock(
            'Magento\Framework\Model\Resource\AbstractResource',
            array('_construct', '_getReadAdapter', '_getWriteAdapter', 'getIdFieldName', 'beginTransaction',
                'rollBack'),
            array(),
            '',
            false
        );
        $this->resourceMock->expects($this->any())->method('beginTransaction')->will($this->returnSelf());
    }

    /**
     * Check if validation throws exceptions in case of incorrect input data
     *
     * @param string $exceptionMessage
     * @param array $data
     *
     * @dataProvider exceptionOfValidationDataProvider
     */
    public function testExceptionOfValidation($exceptionMessage, $data)
    {
        $this->setExpectedException('\Magento\Framework\Model\Exception', $exceptionMessage);
        $rate = $this->objectHelper->getObject(
            'Magento\Tax\Model\Calculation\Rate',
            array('resource' => $this->resourceMock)
        );
        $rate->setData($data)->save();
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function exceptionOfValidationDataProvider()
    {
        return [
            'fill all required fields 1' => [
                'exceptionMessage' => 'Please fill all required fields with valid information.',
                'data' => ['zip_is_range' => true, 'zip_from' => '0111', 'zip_to' => '',
                    'code' => '', 'tax_country_id' => '', 'rate' => '', 'tax_postcode' => '']
            ],
            'fill all required fields 2' => [
                'exceptionMessage' => 'Please fill all required fields with valid information.',
                'data' => ['zip_is_range' => '', 'zip_from' => '', 'zip_to' => '',
                    'code' => '', 'tax_country_id' => '', 'rate' => '0.2', 'tax_postcode' => '1234']],
            'positive number' => [
                'exceptionMessage' => 'Rate Percent should be a positive number.',
                'data' => ['zip_is_range' => '', 'zip_from' => '', 'zip_to' => '', 'code' => 'code',
                    'tax_country_id' => 'US', 'rate' => '-1', 'tax_postcode' => '1234']
            ],
            'zip code length' => [
                'exceptionMessage' => 'Maximum zip code length is 9.',
                'data' => ['zip_is_range' => true, 'zip_from' => '1234567890', 'zip_to' => '1234',
                    'code' => 'code', 'tax_country_id' => 'US', 'rate' => '1.1', 'tax_postcode' => '1234']
            ],
            'contain characters' => [
                'exceptionMessage' => 'Zip code should not contain characters other than digits.',
                'data' => ['zip_is_range' => true, 'zip_from' => 'foo', 'zip_to' => '1234', 'code' => 'code',
                    'tax_country_id' => 'US', 'rate' => '1.1', 'tax_postcode' => '1234']
            ],
            'equal or greater' => [
                'exceptionMessage' => 'Range To should be equal or greater than Range From.',
                'data' => ['zip_is_range' => true, 'zip_from' => '321', 'zip_to' => '123', 'code' => 'code',
                    'tax_country_id' => 'US', 'rate' => '1.1', 'tax_postcode' => '1234']
            ],
        ];
    }

}
