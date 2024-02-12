<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Review\Test\Unit\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Review\Helper\Data as HelperData;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManager;

    /**
     * @var HelperData
     */
    private $helper;

    /**
     * @var MockObject|Escaper
     */
    private $escaper;

    /**
     * @var MockObject|FilterManager
     */
    private $filter;

    /**
     * @var MockObject|Context
     */
    private $context;

    /**
     * @var MockObject|ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Setup environment
     */
    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->filter = $this->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->addMethods(['truncate'])
            ->getMock();

        $this->escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfig);

        $this->objectManager = new ObjectManagerHelper($this);
        $this->helper = $this->objectManager->getObject(
            HelperData::class,
            [
                'context' => $this->context,
                'escaper' => $this->escaper,
                'filter' => $this->filter
            ]
        );
    }

    /**
     * Test getDetail() function
     */
    public function testGetDetail()
    {
        $origDetail = "This\nis\na\nstring";
        $expected = "This<br />" . "\n" . "is<br />" . "\n" . "a<br />" . "\n" . "string";

        $this->filter->expects($this->any())->method('truncate')
            ->with($origDetail, ['length' => 50])
            ->willReturn($origDetail);

        $this->assertEquals($expected, $this->helper->getDetail($origDetail));
    }

    /**
     * Test getDetailHtml() function
     */
    public function getDetailHtml()
    {
        $origDetail = "<span>This\nis\na\nstring</span>";
        $origDetailEscapeHtml = "This\nis\na\nstring";
        $expected = "This<br />" . "\n" . "is<br />" . "\n" . "a<br />" . "\n" . "string";

        $this->escaper->expects($this->any())->method('escapeHtml')
            ->with($origDetail)
            ->willReturn($origDetailEscapeHtml);

        $this->filter->expects($this->any())->method('truncate')
            ->with($origDetailEscapeHtml, ['length' => 50])
            ->willReturn($origDetailEscapeHtml);

        $this->assertEquals($expected, $this->helper->getDetail($origDetail));
    }

    /**
     * Test getIsGuestAllowToWrite() function
     */
    public function testGetIsGuestAllowToWrite()
    {
        $this->scopeConfig->expects($this->any())->method('isSetFlag')
            ->with('catalog/review/allow_guest', ScopeInterface::SCOPE_STORE)
            ->willReturn(true);

        $this->assertTrue($this->helper->getIsGuestAllowToWrite());
    }

    /**
     * Test getReviewStatuses() function
     */
    public function testGetReviewStatuses()
    {
        $expected = [
            1 => __('Approved'),
            2 => __('Pending'),
            3 => __('Not Approved')
        ];
        $this->assertEquals($expected, $this->helper->getReviewStatuses());
    }

    /**
     * Test getReviewStatusesOptionArray() function
     */
    public function testGetReviewStatusesOptionArray()
    {
        $expected = [
            ['value' => 1, 'label' => __('Approved')],
            ['value' => 2, 'label' => __('Pending')],
            ['value' => 3, 'label' => __('Not Approved')]
        ];
        $this->assertEquals($expected, $this->helper->getReviewStatusesOptionArray());
    }
}
