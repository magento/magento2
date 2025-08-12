<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Test\Unit\View\Element;

use Magento\Framework\Filter\FilterManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    /**
     * @var FilterManager|MockObject
     */
    private $filterManagerMock;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Template
     */
    private $templateModel;

    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterManagerMock = $this->getMockBuilder(FilterManager::class)
            ->disableOriginalConstructor()
            ->addMethods(['stripTags'])
            ->getMock();

        $this->contextMock->expects($this->once())
            ->method('getFilterManager')
            ->willReturn($this->filterManagerMock);

        $this->templateModel = new Template($this->contextMock);
    }

    /**
     * Test to verify the stripped output from the HTML input.
     *
     * @param $input
     * @param $output
     * @dataProvider tagDataProvider
     */
    public function testStripTags($input, $output): void
    {
        if ($input) {
            $this->filterManagerMock->expects($this->once())
                ->method('stripTags')
                ->with($input)
                ->willReturn($output);
        }
        $this->assertEquals($this->templateModel->stripTags($input), $output);
    }

    /**
     * @return array
     */
    public static function tagDataProvider(): array
    {
        return [
            ['-1', '-1'],
            ['0', '0'],
            ['1', '1'],
            ['Hello <b>world!</b>' , 'Hello world!']
        ];
    }
}
