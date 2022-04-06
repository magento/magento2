<?php declare(strict_types=1);
/**
 * test Magento\Customer\Model\Metadata\Form\AbstractData
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata\Form;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\ValidationRuleInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractDataTest extends TestCase
{
    const MODEL = 'MODEL';

    /**
     * @var ExtendsAbstractData
     */
    private $model;

    /**
     * @var MockObject|TimezoneInterface
     */
    private $localeMock;

    /**
     * @var MockObject|ResolverInterface
     */
    private $localeResolverMock;

    /**
     * @var MockObject|LoggerInterface
     */
    private $loggerMock;

    /**
     * @var MockObject|AttributeMetadataInterface
     */
    private $attributeMock;

    /**
     * @var string
     */
    private $value;

    /**
     * @var string
     */
    private $entityTypeCode;

    /**
     * @var string
     */
    private $isAjax;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->localeMock = $this->getMockBuilder(
            TimezoneInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->localeResolverMock = $this->getMockBuilder(
            ResolverInterface::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        $this->attributeMock = $this->getMockForAbstractClass(AttributeMetadataInterface::class);
        $this->value = 'VALUE';
        $this->entityTypeCode = 'ENTITY_TYPE_CODE';
        $this->isAjax = false;

        $this->model = new ExtendsAbstractData(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMock,
            $this->localeResolverMock,
            $this->value,
            $this->entityTypeCode,
            $this->isAjax
        );
    }

    /**
     * @return void
     */
    public function testGetAttribute(): void
    {
        $this->assertSame($this->attributeMock, $this->model->getAttribute());
    }

    /**
     * @return void
     */
    public function testGetAttributeException(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Attribute object is undefined');

        $this->model->setAttribute(false);
        $this->model->getAttribute();
    }

    /**
     * @return void
     */
    public function testSetRequestScope(): void
    {
        $this->assertSame($this->model, $this->model->setRequestScope('REQUEST_SCOPE'));
        $this->assertSame('REQUEST_SCOPE', $this->model->getRequestScope());
    }

    /**
     * @param bool $bool
     *
     * @return void
     * @dataProvider trueFalseDataProvider
     */
    public function testSetRequestScopeOnly($bool): void
    {
        $this->assertSame($this->model, $this->model->setRequestScopeOnly($bool));
        $this->assertSame($bool, $this->model->isRequestScopeOnly());
    }

    /**
     * @return array
     */
    public function trueFalseDataProvider(): array
    {
        return [[true], [false]];
    }

    /**
     * @return void
     */
    public function testGetSetExtractedData(): void
    {
        $data = ['KEY' => 'VALUE'];
        $this->assertSame($this->model, $this->model->setExtractedData($data));
        $this->assertSame($data, $this->model->getExtractedData());
        $this->assertSame('VALUE', $this->model->getExtractedData('KEY'));
        $this->assertNull($this->model->getExtractedData('BAD_KEY'));
    }

    /**
     * @param bool|string $input
     * @param bool|string $output
     * @param bool|string $filter
     *
     * @return void
     * @dataProvider applyInputFilterProvider
     */
    public function testApplyInputFilter($input, $output, $filter): void
    {
        if ($input) {
            $this->attributeMock->expects($this->once())->method('getInputFilter')->willReturn($filter);
        }
        $this->assertEquals($output, $this->model->applyInputFilter($input));
    }

    /**
     * @return array
     */
    public function applyInputFilterProvider(): array
    {
        return [
            [false, false, false],
            [true, true, false],
            ['string', 'string', false],
            ['2014/01/23', '2014-01-23', 'date'],
            ['<tag>internal text</tag>', 'internal text', 'striptags']
        ];
    }

    /**
     * @param null|bool|string $format
     * @param string           $output
     *
     * @return void
     * @dataProvider dateFilterFormatProvider
     */
    public function testDateFilterFormat($format, $output): void
    {
        // Since model is instantiated in setup, if I use it directly in the dataProvider, it will be null.
        // I use this value to indicate the model is to be used for output
        if (self::MODEL == $output) {
            $output = $this->model;
        }
        if ($format === null) {
            $this->localeMock->expects(
                $this->once()
            )->method(
                'getDateFormat'
            )->with(
                \IntlDateFormatter::SHORT
            )->willReturn(
                $output
            );
        }
        $actual = $this->model->dateFilterFormat($format);
        $this->assertEquals($output, $actual);
    }

    /**
     * @return array
     */
    public function dateFilterFormatProvider(): array
    {
        return [[null, 'Whatever I put'], [false, self::MODEL], ['something else', self::MODEL]];
    }

    /**
     * @param bool|string $input
     * @param bool|string $output
     * @param bool|string $filter
     *
     * @return void
     * @dataProvider applyOutputFilterDataProvider
     */
    public function testApplyOutputFilter($input, $output, $filter): void
    {
        if ($input) {
            $this->attributeMock->expects($this->once())->method('getInputFilter')->willReturn($filter);
        }
        $this->assertEquals($output, $this->model->applyOutputFilter($input));
    }

    /**
     * This is similar to applyInputFilterProvider except for striptags.
     *
     * @return array
     */
    public function applyOutputFilterDataProvider(): array
    {
        return [
            [false, false, false],
            [true, true, false],
            ['string', 'string', false],
            ['2014/01/23', '2014-01-23', 'date'],
            ['internal text', 'internal text', 'striptags']
        ];
    }

    /**
     * Tests input validation rules.
     *
     * @param null|string $value
     * @param null|string $label
     * @param null|string $inputValidation
     * @param bool|array  $expectedOutput
     *
     * @return void
     * @dataProvider validateInputRuleDataProvider
     */
    public function testValidateInputRule($value, $label, $inputValidation, $expectedOutput): void
    {
        $validationRule = $this->getMockBuilder(ValidationRuleInterface::class)->disableOriginalConstructor()
            ->onlyMethods(['getName', 'getValue'])
            ->getMockForAbstractClass();

        $validationRule->method('getName')
            ->willReturn('input_validation');

        $validationRule->method('getValue')
            ->willReturn($inputValidation);

        $this->attributeMock->method('getStoreLabel')
            ->willReturn($label);

        $this->attributeMock->method('getValidationRules')
            ->willReturn([$validationRule]);

        $this->assertEquals($expectedOutput, $this->model->validateInputRule($value));
    }

    /**
     * @return array
     */
    public function validateInputRuleDataProvider(): array
    {
        return [
            [null, null, null, true],
            ['value', null, null, true],
            [
                '!@#$',
                'mylabel',
                'alphanumeric',
                [
                    \Zend_Validate_Alnum::NOT_ALNUM => '"mylabel" contains non-alphabetic or non-numeric characters.'
                ]
            ],
            [
                'abc qaz',
                'mylabel',
                'alphanumeric',
                [
                    \Zend_Validate_Alnum::NOT_ALNUM => '"mylabel" contains non-alphabetic or non-numeric characters.'
                ]
            ],
            ['abcqaz', 'mylabel', 'alphanumeric', true],
            ['abc qaz', 'mylabel', 'alphanum-with-spaces', true],
            [
                '!@#$',
                'mylabel',
                'numeric',
                [\Zend_Validate_Digits::NOT_DIGITS => '"mylabel" contains non-numeric characters.']
            ],
            [
                '1234',
                'mylabel',
                'alpha',
                [\Zend_Validate_Alpha::NOT_ALPHA => '"mylabel" contains non-alphabetic characters.']
            ],
            [
                '!@#$',
                'mylabel',
                'email',
                [
                    // @codingStandardsIgnoreStart
                    \Zend_Validate_EmailAddress::INVALID_HOSTNAME => '"mylabel" is not a valid hostname.',
                    \Zend_Validate_Hostname::INVALID_HOSTNAME => "'#\$' does not match the expected structure for a DNS hostname",
                    \Zend_Validate_Hostname::INVALID_LOCAL_NAME => "'#\$' does not look like a valid local network name."
                    // @codingStandardsIgnoreEnd
                ]
            ],
            ['1234', 'mylabel', 'url', ['"mylabel" is not a valid URL.']],
            ['http://.com', 'mylabel', 'url', ['"mylabel" is not a valid URL.']],
            [
                '1234',
                'mylabel',
                'date',
                [\Zend_Validate_Date::INVALID_DATE => '"mylabel" is not a valid date.']
            ]
        ];
    }

    /**
     * @param bool $ajaxRequest
     *
     * @return void
     * @dataProvider trueFalseDataProvider
     */
    public function testGetIsAjaxRequest($ajaxRequest): void
    {
        $this->model = new ExtendsAbstractData(
            $this->localeMock,
            $this->loggerMock,
            $this->attributeMock,
            $this->localeResolverMock,
            $this->value,
            $this->entityTypeCode,
            $ajaxRequest
        );
        $this->assertSame($ajaxRequest, $this->model->getIsAjaxRequest());
    }

    /**
     * @param RequestInterface $request
     * @param string                        $attributeCode
     * @param bool|string                   $requestScope
     * @param bool                          $requestScopeOnly
     * @param string                        $expectedValue
     *
     * @return void
     * @dataProvider getRequestValueDataProvider
     */
    public function testGetRequestValue(
        $request,
        $attributeCode,
        $requestScope,
        $requestScopeOnly,
        $expectedValue
    ): void {
        $this->attributeMock->expects(
            $this->once()
        )->method(
            'getAttributeCode'
        )->willReturn(
            $attributeCode
        );
        $this->model->setRequestScope($requestScope);
        $this->model->setRequestScopeOnly($requestScopeOnly);
        $this->assertEquals($expectedValue, $this->model->getRequestValue($request));
    }

    /**
     * @return array
     */
    public function getRequestValueDataProvider(): array
    {
        $expectedValue = 'EXPECTED_VALUE';
        $requestMock = $this->getMockBuilder(RequestInterface::class)
            ->getMock();
        $requestMock->method('getParam')
            ->withConsecutive(['ATTR_CODE'], ['REQUEST_SCOPE'], ['REQUEST_SCOPE'])
            ->willReturn($expectedValue, ['ATTR_CODE' => $expectedValue], []);

        $requestMockHttp = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();
        $requestMockHttp
            ->expects($this->once())
            ->method('getParams')
            ->willReturn(['REQUEST' => ['SCOPE' => ['ATTR_CODE' => $expectedValue]]]);

        return [
            [$requestMock, 'ATTR_CODE', false, false, $expectedValue],
            [$requestMock, 'ATTR_CODE', 'REQUEST_SCOPE', false, $expectedValue],
            [$requestMock, 'ATTR_CODE', 'REQUEST_SCOPE', false, false],
            [$requestMockHttp, 'ATTR_CODE', 'REQUEST/SCOPE', false, $expectedValue]
        ];
    }
}
