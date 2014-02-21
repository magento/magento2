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
namespace Magento\Customer\Block\Widget;

use Magento\Core\Model\LocaleInterface;
use Magento\Exception\NoSuchEntityException;

class DobTest extends \PHPUnit_Framework_TestCase
{
    /** Constants used in the unit tests */
    const MIN_DATE = '01/01/2010';
    const MAX_DATE = '01/01/2020';
    const DATE = '01/01/2014';
    const DAY = '01';    // Value of date('d', strtotime(self::DATE))
    const MONTH = '01';  // Value of date('m', strtotime(self::DATE))
    const YEAR = '2014'; // Value of date('Y', strtotime(self::DATE))
    const DATE_FORMAT = 'M/d/yy';

    /** Constants used by Dob::setDateInput($code, $html) */
    const DAY_HTML =
        '<div><label for="day"><span>d</span></label><input type="text" id="day" name="Day" value="1"></div>';
    const MONTH_HTML =
        '<div><label for="month"><span>M</span></label><input type="text" id="month" name="Month" value="jan"></div>';
    const YEAR_HTML =
        '<div><label for="year"><span>yy</span></label><input type="text" id="year" name="Year" value="14"></div>';

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata */
    private $_attribute;

    /** @var Dob */
    private $_block;

    /** @var \PHPUnit_Framework_MockObject_MockObject | \Magento\Customer\Service\V1\CustomerMetadataServiceInterface */
    private $_metadataService;

    public function setUp()
    {
        $zendCacheCore = new \Zend_Cache_Core();
        $zendCacheCore->setBackend(new \Zend_Cache_Backend_BlackHole());

        $frontendCache = $this->getMockForAbstractClass('Magento\Cache\FrontendInterface', [], '', false);
        $frontendCache->expects($this->any())
            ->method('getLowLevelFrontend')->will($this->returnValue($zendCacheCore));
        $app = $this->getMock('Magento\Core\Model\App', [], [], '', false);
        $app->expects($this->any())->method('getCache')->will($this->returnValue($frontendCache));

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $locale = $objectManager
            ->getObject('Magento\Core\Model\Locale', ['app' => $app, 'locale' => LocaleInterface::DEFAULT_LOCALE]);

        $context = $this->getMock('Magento\View\Element\Template\Context', [], [], '', false);
        $context->expects($this->any())->method('getLocale')->will($this->returnValue($locale));

        $this->_attribute = $this->getMock('Magento\Customer\Service\V1\Dto\Eav\AttributeMetadata', [], [], '', false);
        $this->_metadataService =
            $this->getMockForAbstractClass(
                'Magento\Customer\Service\V1\CustomerMetadataServiceInterface', [], '', false
            );
        $this->_metadataService
            ->expects($this->any())->method('getAttributeMetadata')->will($this->returnValue($this->_attribute));

        date_default_timezone_set('America/Los_Angeles');

        $this->_block = new Dob(
            $context,
            $this->getMock('Magento\Customer\Helper\Address', [], [], '', false),
            $this->_metadataService
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
        $this->_attribute->expects($this->once())->method('isVisible')->will($this->returnValue($isVisible));
        $this->assertSame($expectedValue, $this->_block->isEnabled());
    }

    /**
     * @return array
     */
    public function isEnabledDataProvider()
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    public function testIsEnabledWithException()
    {
        $this->_metadataService
            ->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->throwException(new NoSuchEntityException('field', 'value')));
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
        $this->_attribute->expects($this->once())->method('isRequired')->will($this->returnValue($isRequired));
        $this->assertSame($expectedValue, $this->_block->isRequired());
    }

    public function testIsRequiredWithException()
    {
        $this->_metadataService
            ->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->throwException(new NoSuchEntityException('field', 'value')));
        $this->assertSame(false, $this->_block->isRequired());
    }

    /**
     * @return array
     */
    public function isRequiredDataProvider()
    {
        return [
            [true, true],
            [false, false]
        ];
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
        $this->assertEquals($expectedDate, $this->_block->getData('date'));
    }

    /**
     * @return array
     */
    public function setDateDataProvider()
    {
        return [
            [self::DATE, strtotime(self::DATE), self::DATE],
            [false, false, false]
        ];
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
        return [
            [self::DATE, self::DAY],
            [false, '']
        ];
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
        return [
            [self::DATE, self::MONTH],
            [false, '']
        ];
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
        return [
            [self::DATE, self::YEAR],
            [false, '']
        ];
    }

    /**
     * The LocaleInterface::DEFAULT_LOCALE is used to derive the Locale that is used to determine the
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

        $this->assertEquals(
            self::MONTH_HTML . self::DAY_HTML . self::YEAR_HTML, $this->_block->getSortedDateInputs());
    }

    /**
     * @param array $validationRules The date Min/Max validation rules
     * @param int $expectedValue The value we expect from Dob::getMinDateRange()
     *
     * @dataProvider getMinDateRangeDataProvider
     */
    public function testGetMinDateRange($validationRules, $expectedValue)
    {
        $this->_attribute
            ->expects($this->once())->method('getValidationRules')->will($this->returnValue($validationRules));
        $this->assertEquals($expectedValue, $this->_block->getMinDateRange());
    }

    /**
     * @return array
     */
    public function getMinDateRangeDataProvider()
    {
        return [
            [[Dob::MIN_DATE_RANGE_KEY => strtotime(self::MIN_DATE)], date('Y/m/d', strtotime(self::MIN_DATE))],
            [[], null]
        ];
    }

    public function testGetMinDateRangeWithException()
    {
        $this->_metadataService
            ->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->throwException(new NoSuchEntityException('field', 'value')));
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
        $this->_attribute
            ->expects($this->once())->method('getValidationRules')->will($this->returnValue($validationRules));
        $this->assertEquals($expectedValue, $this->_block->getMaxDateRange());
    }

    /**
     * @return array
     */
    public function getMaxDateRangeDataProvider()
    {
        return [
            [[Dob::MAX_DATE_RANGE_KEY => strtotime(self::MAX_DATE)], date('Y/m/d', strtotime(self::MAX_DATE))],
            [[], null]
        ];
    }

    public function testGetMaxDateRangeWithException()
    {
        $this->_metadataService
            ->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->throwException(new NoSuchEntityException('field', 'value')));
        $this->assertNull($this->_block->getMaxDateRange());
    }
}
