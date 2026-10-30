<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);


namespace Magento\Wishlist\Test\Unit\Block\Adminhtml\Widget\Grid\Column\Filter;

use Magento\Backend\Block\Context;
use Magento\Framework\DB\Helper;
use Magento\Wishlist\Block\Adminhtml\Widget\Grid\Column\Filter\Text;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    /** @var Text */
    private $textFilterBlock;

    /** @var Context|MockObject */
    private $contextMock;

    /** @var Helper|MockObject */
    private $helperMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->helperMock = $this->createMock(Helper::class);

        $this->textFilterBlock = new Text(
            $this->contextMock,
            $this->helperMock
        );
    }

    public function testGetCondition()
    {
        $value = "test";
        $this->textFilterBlock->setValue($value);
        $this->assertSame(["like" => $value], $this->textFilterBlock->getCondition());
    }
}
