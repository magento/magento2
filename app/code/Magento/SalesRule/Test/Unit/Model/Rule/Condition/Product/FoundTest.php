<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model\Rule\Condition\Product;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item;
use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Magento\SalesRule\Model\Rule\Condition\Product\Found;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FoundTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var MockObject|Context
     */
    protected $contextMock;

    /**
     * @var MockObject|Product
     */
    protected $conditionProductMock;

    /**
     * @var Found
     */
    protected $foundValidator;

    /**
     * @var MockObject|LoggerInterface
     */
    protected $logger;

    /**
     * Setup test
     */
    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->logger = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->contextMock->method('getLogger')->willReturn($this->logger);
    }


    /**
     * Test validation of a valid quote item.
     *
     * @dataProvider dataProviderValidate
     *
     * @param string $aggregator
     * @param bool $value
     * @param bool $isValid
     * @param bool $expectedResult
     */
    public function testValidate($aggregator, $value, $isValid, $expectedResult)
    {
        $this->prepareValidator($aggregator, $value);
        $this->conditionProductMock->expects($this->once())
            ->method('validate')
            ->willReturn($isValid);
        $quoteItem = $this->createPartialMock(
            Item::class,
            []
        );
        $quoteAddress = $this->createPartialMock(
            Address::class,
            ['getAllItems']
        );
        $quoteAddress->expects($this->once())->method('getAllItems')
            ->willReturn([$quoteItem]);
        $this->assertEquals($expectedResult, $this->foundValidator->validate($quoteAddress));
    }

    /**
     * Test validation of a quote item referring
     * to a non-existent product.
     *
     * @dataProvider dataProviderValidate
     *
     * @param string $aggregator
     * @param bool $value
     * @param bool $isValid
     */
    public function testValidateNonExistentProduct($aggregator, $value)
    {
        $this->prepareValidator($aggregator, $value);
        $this->conditionProductMock->expects($this->once())
            ->method('validate')
            ->willThrowException(new NoSuchEntityException(new Phrase('Entity Id does not exist')));
        $quoteItem = $this->createPartialMock(
            Item::class,
            []
        );
        $quoteAddress = $this->createPartialMock(
            Address::class,
            ['getAllItems']
        );
        $quoteAddress->expects($this->once())->method('getAllItems')
            ->willReturn([$quoteItem]);
        $this->logger->expects($this->once())->method('error');
        $this->assertEquals(false, $this->foundValidator->validate($quoteAddress));
    }

    /**
     * Validate data provider.
     *
     * @return array
     */
    public function dataProviderValidate()
    {
        return [
            [
                'all',
                true,
                true,
                true
            ],
            [
                'all',
                true,
                false,
                false
            ],
            [
                'all',
                false,
                true,
                false
            ],
                        [
                'all',
                false,
                false,
                true
            ],
            [
                'any',
                true,
                true,
                true
            ],
            [
                'any',
                true,
                false,
                false
            ],
            [
                'any',
                false,
                true,
                false
            ],
            [
                'any',
                false,
                false,
                true
            ]
        ];
    }

    /**
     * Prepare validator.
     *
     * @param $aggregator
     * @param $value
     */
    private function prepareValidator($aggregator, $value)
    {
        $this->conditionProductMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->foundValidator = $this->objectManager
            ->getObject(
                Found::class,
                [
                    'context' => $this->contextMock,
                    'ruleConditionProduct' => $this->conditionProductMock,
                    'data' => [],
                ]
            );
        $this->foundValidator->setData([
            'aggregator' => $aggregator,
            'value' => $value,
        ]);
        $this->foundValidator->setConditions([$this->conditionProductMock]);
    }
}
