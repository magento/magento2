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

namespace Magento\Customer\Model\Attribute\Data;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

class PostcodeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DirectoryHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $directoryHelperMock;

    /**
     * @var AbstractAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $attributeMock;

    protected function setUp()
    {
        $this->localeMock = $this->getMockBuilder('Magento\Framework\Stdlib\DateTime\TimezoneInterface')
            ->getMock();
        $this->localeResolverMock = $this->getMockBuilder('Magento\Framework\Locale\ResolverInterface')
            ->getMock();
        $this->loggerMock = $this->getMockBuilder('Magento\Framework\Logger')
            ->disableOriginalConstructor()
            ->getMock();
        $this->directoryHelperMock = $this->getMockBuilder('Magento\Directory\Helper\Data')
            ->disableOriginalConstructor()
            ->getMock();
        $this->attributeMock = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->disableOriginalConstructor()
            ->setMethods(['getStoreLabel'])
            ->getMock();
    }

    /**
     * Create an instance of the class that is being tested
     *
     * @param string|int|bool|null $value The value undergoing testing by a given test
     * @return Postcode
     */
    protected function getClass($value)
    {
        return new Postcode(
            $this->localeMock,
            $this->loggerMock,
            $this->localeResolverMock,
            $this->directoryHelperMock
        );
    }

    /**
     * @param string $value to assign to boolean
     * @param bool $expected text output
     * @param string $countryId
     * @param bool $isOptional
     *
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue($value, $expected, $countryId, $isOptional)
    {
        $storeLabel = 'Zip/Postal Code';
        $this->attributeMock->expects($this->once())
            ->method('getStoreLabel')
            ->willReturn($storeLabel);

        $this->directoryHelperMock->expects($this->once())
            ->method('isZipCodeOptional')
            ->willReturnMap([
                [$countryId, $isOptional]
            ]);

        $object = $this->getClass($value);
        $object->setAttribute($this->attributeMock);
        $object->setExtractedData(['country_id' => $countryId]);

        $actual = $object->validateValue($value);
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function validateValueDataProvider()
    {
        return [
            ['', ['"Zip/Postal Code" is a required value.'], 'US', false],
            ['90034', true, 'US', false],
            ['', true, 'IE', true],
            ['90034', true, 'IE', true],
        ];
    }
}
