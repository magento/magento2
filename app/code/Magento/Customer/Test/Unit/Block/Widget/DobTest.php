<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Customer\Test\Unit\Block\Widget;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Block\Widget\Dob;
use Magento\Framework\Locale\Resolver;

class DobTest extends \PHPUnit_Framework_TestCase
{
    /** Constants used in the unit tests */
    const MIN_DATE = '01/01/2010';

    const MAX_DATE = '01/01/2020';

    const DATE = '01/01/2014';

    const DAY = '01';

    // Value of date('d', strtotime(self::DATE))
    const MONTH = '01';

    // Value of date('m', strtotime(self::DATE))
    const YEAR = '2014';

    // Value of date('Y', strtotime(self::DATE))
    const DATE_FORMAT = 'M/d/yy';

    /** Constants used by Dob::setDateInput($code, $html) */
    const DAY_HTML =
        '<div><label for="day"><span>d</span></label><input type="text" id="day" name="Day" value="1"></div>';

    const MONTH_HTML =
        '<div><label for="month"><span>M</span></label><input type="text" id="month" name="Month" value="jan"></div>';

    const YEAR_HTML =
        '<div><label for="year"><span>yy</span></label><input type="text" id="year" name="Year" value="14"></div>';

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Api\Data\AttributeMetadataInterface */
    protected $attribute;

    /** @var Dob */
    protected $_block;

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Customer\Api\CustomerMetadataInterface */
    protected $customerMetadata;

    /**
     * @var \Magento\Framework\Data\Form\FilterFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterFactory;

    protected function setUp()
    {
        $zendCacheCore = new \Zend_Cache_Core();
        $zendCacheCore->setBackend(new \Zend_Cache_Backend_BlackHole());

        $frontendCache = $this->getMockForAbstractClass(
            'Magento\Framework\Cache\FrontendInterface',
            [],
            '',
            false
        );
        $frontendCache->expects($this->any())->method('getLowLevelFrontend')->will($this->returnValue($zendCacheCore));
        $cache = $this->getMock('Magento\Framework\App\CacheInterface');
        $cache->expects($this->any())->method('getFrontend')->will($this->returnValue($frontendCache));

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $localeResolver = $this->getMock('\Magento\Framework\Locale\ResolverInterface');
        $localeResolver->expects($this->any())
            ->method('getLocale')
            ->willReturn(Resolver::DEFAULT_LOCALE);
        $timezone = $objectManager->getObject(
            'Magento\Framework\Stdlib\DateTime\Timezone',
            ['localeResolver' => $localeResolver]
        );

        $context = $this->getMock('Magento\Framework\View\Element\Template\Context', [], [], '', false);
        $context->expects($this->any())->method('getLocaleDate')->will($this->returnValue($timezone));

        $this->attribute = $this->getMockBuilder('\Magento\Customer\Api\Data\AttributeMetadataInterface')
            ->getMockForAbstractClass();
        $this->customerMetadata = $this->getMockBuilder('\Magento\Customer\Api\CustomerMetadataInterface')
            ->getMockForAbstractClass();
        $this->customerMetadata->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->returnValue($this->attribute));

        date_default_timezone_set('America/Los_Angeles');

        $this->filterFactory = $this->getMockBuilder('Magento\Framework\Data\Form\FilterFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->_block = new \Magento\Customer\Block\Widget\Dob(
            $context,
            $this->getMock('Magento\Customer\Helper\Address', [], [], '', false),
            $this->customerMetadata,
            $this->getMock('Magento\Framework\View\Element\Html\Date', [], [], '', false),
            $this->filterFactory
        );
    }

    /**
     * @param bool $isVisible Determines whether the 'dob' attribute is visible or enabled
     * @param bool $expectedValue The value we expect from Dob::isEnabled()
     *
     * @dataProvider isEnabledDataProvider
     */
    public function testIsEnabled($isVisible, $expectedValue)
    {
        $this->attribute->expects($this->once())->method('isVisible')->will($this->returnValue($isVisible));
        $this->assertSame($expectedValue, $this->_block->isEnabled());
    }

    /**
     * @return array
     */
    public function isEnabledDataProvider()
    {
        return [[true, true], [false, false]];
    }

    public function testIsEnabledWithException()
    {
        $this->customerMetadata->expects($this->any())
            ->method('getAttributeMetadata')
            ->will(
                $this->throwException(new NoSuchEntityException(
                    __(
                        'No such entity with %fieldName = %fieldValue',
                        ['fieldName' => 'field', 'fieldValue' => 'value']
                    )
                ))
            );
        $this->assertSame(false, $this->_block->isEnabled());
    }

    /**
     * @param bool $isRequired Determines whether the 'dob' attribute is required
     * @param bool $expectedValue The value we expect from Dob::isRequired()
     *
     * @dataProvider isRequiredDataProvider
     */
    public function testIsRequired($isRequired, $expectedValue)
    {
        $this->attribute->expects($this->once())->method('isRequired')->will($this->returnValue($isRequired));
        $this->assertSame($expectedValue, $this->_block->isRequired());
    }

    public function testIsRequiredWithException()
    {
        $this->customerMetadata->expects($this->any())
            ->method('getAttributeMetadata')
            ->will(
                $this->throwException(new NoSuchEntityException(
                    __(
                        'No such entity with %fieldName = %fieldValue',
                        ['fieldName' => 'field', 'fieldValue' => 'value']
                    )
                ))
            );
        $this->assertSame(false, $this->_block->isRequired());
    }

    /**
     * @return array
     */
    public function isRequiredDataProvider()
    {
        return [[true, true], [false, false]];
    }

    /**
     * @param string|bool $date Date (e.g. '01/01/2020' or false for no date)
     * @param int|bool $expectedTime The value we expect from Dob::getTime()
     * @param string|bool $expectedDate The value we expect from Dob::getData('date')
     *
     * @dataProvider setDateDataProvider
     */
    public function testSetDate($date, $expectedTime, $expectedDate)
    {
        $this->assertSame($this->_block, $this->_block->setDate($date));
        $this->assertEquals($expectedTime, $this->_block->getTime());
        $this->assertEquals($expectedDate, $this->_block->getValue());
    }

    /**
     * @return array
     */
    public function setDateDataProvider()
    {
        return [[self::DATE, strtotime(self::DATE), self::DATE], [false, false, false]];
    }

    public function testSetDateWithFilter()
    {
        $date = '2014-01-01';
        $filterCode = 'date';

        $this->attribute->expects($this->once())
            ->method('getInputFilter')
            ->willReturn($filterCode);

        $filterMock = $this->getMockBuilder('Magento\Framework\Data\Form\Filter\Date')
            ->disableOriginalConstructor()
            ->getMock();
        $filterMock->expects($this->once())
            ->method('outputFilter')
            ->with($date)
            ->willReturn(self::DATE);

        $this->filterFactory->expects($this->once())
            ->method('create')
            ->with($filterCode, ['format' => self::DATE_FORMAT])
            ->willReturn($filterMock);

        $this->_block->setDate($date);
    }

    /**
     * @param string|bool $date The date (e.g. '01/01/2020' or false for no date)
     * @param string $expectedDay The value we expect from Dob::getDay()
     *
     * @dataProvider getDayDataProvider
     */
    public function testGetDay($date, $expectedDay)
    {
        $this->_block->setDate($date);
        $this->assertEquals($expectedDay, $this->_block->getDay());
    }

    /**
     * @return array
     */
    public function getDayDataProvider()
    {
        return [[self::DATE, self::DAY], [false, '']];
    }

    /**
     * @param string|bool $date The date (e.g. '01/01/2020' or false for no date)
     * @param string $expectedMonth The value we expect from Dob::getMonth()
     *
     * @dataProvider getMonthDataProvider
     */
    public function testGetMonth($date, $expectedMonth)
    {
        $this->_block->setDate($date);
        $this->assertEquals($expectedMonth, $this->_block->getMonth());
    }

    /**
     * @return array
     */
    public function getMonthDataProvider()
    {
        return [[self::DATE, self::MONTH], [false, '']];
    }

    /**
     * @param string|bool $date The date (e.g. '01/01/2020' or false for no date)
     * @param string $expectedYear The value we expect from Dob::getYear()
     *
     * @dataProvider getYearDataProvider
     */
    public function testGetYear($date, $expectedYear)
    {
        $this->_block->setDate($date);
        $this->assertEquals($expectedYear, $this->_block->getYear());
    }

    /**
     * @return array
     */
    public function getYearDataProvider()
    {
        return [[self::DATE, self::YEAR], [false, '']];
    }

    /**
     * The \Magento\Framework\Locale\ResolverInterface::DEFAULT_LOCALE
     * is used to derive the Locale that is used to determine the
     * value of Dob::getDateFormat() for that Locale.
     */
    public function testGetDateFormat()
    {
        $this->assertEquals(self::DATE_FORMAT, $this->_block->getDateFormat());
    }

    /**
     * This tests the Dob::setDateInput() method. The Dob::getSortedDateInputs() uses the value of
     * Dob::getDateFormat() to derive the return value, which is equivalent to self::DATE_FORMAT.
     */
    public function testGetSortedDateInputs()
    {
        $this->_block->setDateInput('d', self::DAY_HTML);
        $this->_block->setDateInput('m', self::MONTH_HTML);
        $this->_block->setDateInput('y', self::YEAR_HTML);

        $this->assertEquals(self::MONTH_HTML . self::DAY_HTML . self::YEAR_HTML, $this->_block->getSortedDateInputs());
    }

    /**
     * This tests the Dob::setDateInput() method. The Dob::getSortedDateInputs() uses the value of
     * Dob::getDateFormat() to derive the return value, which is equivalent to self::DATE_FORMAT.
     */
    public function testGetSortedDateInputsWithoutStrippingNonInputChars()
    {
        $this->_block->setDateInput('d', self::DAY_HTML);
        $this->_block->setDateInput('m', self::MONTH_HTML);
        $this->_block->setDateInput('y', self::YEAR_HTML);

        $this->assertEquals(
            self::MONTH_HTML . '/' . self::DAY_HTML . '/' . self::YEAR_HTML,
            $this->_block->getSortedDateInputs(false)
        );
    }

    /**
     * @param array $validationRules The date Min/Max validation rules
     * @param int $expectedValue The value we expect from Dob::getMinDateRange()
     *
     * @dataProvider getMinDateRangeDataProvider
     */
    public function testGetMinDateRange($validationRules, $expectedValue)
    {
        $this->attribute->expects(
            $this->once()
        )->method(
                'getValidationRules'
            )->will(
                $this->returnValue($validationRules)
            );
        $this->assertEquals($expectedValue, $this->_block->getMinDateRange());
    }

    /**
     * @return array
     */
    public function getMinDateRangeDataProvider()
    {
        $emptyValidationRule = $this->getMockBuilder('Magento\Customer\Api\Data\ValidationRuleInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getValue'])
            ->getMockForAbstractClass();

        $validationRule = $this->getMockBuilder('Magento\Customer\Api\Data\ValidationRuleInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getValue'])
            ->getMockForAbstractClass();
        $validationRule->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(Dob::MIN_DATE_RANGE_KEY));
        $validationRule->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(strtotime(self::MIN_DATE)));

        return [
            [
                [
                    $validationRule,
                ],
                date('Y/m/d', strtotime(self::MIN_DATE)),
            ],
            [
                [
                    $emptyValidationRule,
                ],
                null
            ]
        ];
    }

    public function testGetMinDateRangeWithException()
    {
        $this->customerMetadata->expects($this->any())
            ->method('getAttributeMetadata')
            ->will(
                $this->throwException(new NoSuchEntityException(
                    __(
                        'No such entity with %fieldName = %fieldValue',
                        ['fieldName' => 'field', 'fieldValue' => 'value']
                    )
                ))
            );
        $this->assertNull($this->_block->getMinDateRange());
    }

    /**
     * @param array $validationRules The date Min/Max validation rules
     * @param int $expectedValue The value we expect from Dob::getMaxDateRange()
     *
     * @dataProvider getMaxDateRangeDataProvider
     */
    public function testGetMaxDateRange($validationRules, $expectedValue)
    {
        $this->attribute->expects(
            $this->once()
        )->method(
                'getValidationRules'
            )->will(
                $this->returnValue($validationRules)
            );
        $this->assertEquals($expectedValue, $this->_block->getMaxDateRange());
    }

    /**
     * @return array
     */
    public function getMaxDateRangeDataProvider()
    {
        $emptyValidationRule = $this->getMockBuilder('Magento\Customer\Api\Data\ValidationRuleInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getValue'])
            ->getMockForAbstractClass();

        $validationRule = $this->getMockBuilder('Magento\Customer\Api\Data\ValidationRuleInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getName', 'getValue'])
            ->getMockForAbstractClass();
        $validationRule->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(Dob::MAX_DATE_RANGE_KEY));
        $validationRule->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue(strtotime(self::MAX_DATE)));
        return [
            [
                [
                    $validationRule,
                ],
                date('Y/m/d', strtotime(self::MAX_DATE)),
            ],
            [
                [
                    $emptyValidationRule,
                ],
                null
            ]
        ];
    }

    public function testGetMaxDateRangeWithException()
    {
        $this->customerMetadata->expects($this->any())
            ->method('getAttributeMetadata')
            ->will(
                $this->throwException(new NoSuchEntityException(
                    __(
                        'No such entity with %fieldName = %fieldValue',
                        ['fieldName' => 'field', 'fieldValue' => 'value']
                    )
                ))
            );
        $this->assertNull($this->_block->getMaxDateRange());
    }
}
