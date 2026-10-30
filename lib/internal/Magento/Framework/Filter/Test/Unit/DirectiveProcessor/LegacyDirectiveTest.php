<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit\DirectiveProcessor;

use Magento\Framework\Filter\DirectiveProcessor\LegacyDirective;
use Magento\Framework\Filter\DirectiveProcessor\SimpleDirective;
use Magento\Framework\Filter\Template;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

class LegacyDirectiveTest extends TestCase
{
    /**
     * @var SimpleDirective&MockObject
     */
    private $simpleDirective;

    /**
     * @var Template&Stub
     */
    private $filter;

    /**
     * @var LegacyDirective
     */
    private $legacyDirective;

    protected function setUp(): void
    {
        $this->simpleDirective = $this->getMockBuilder(SimpleDirective::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['process'])
            ->getMock();
        $this->filter = $this->createStub(Template::class);
        $this->legacyDirective = new LegacyDirective($this->simpleDirective);
    }

    /**
     * @param string $text
     * @return void
     */
    #[DataProvider('unmatchedConstructionProvider')]
    public function testUnmatchedConstructionIsReturnedUnchanged(string $text): void
    {
        preg_match($this->legacyDirective->getRegularExpression(), $text, $construction);
        $this->simpleDirective->expects($this->never())->method('process');

        $this->assertSame($text, $this->legacyDirective->process($construction, $this->filter, []));
    }

    /**
     * @return array
     */
    public static function unmatchedConstructionProvider(): array
    {
        return [
            'space before name' => ['{{ price }}'],
            'empty' => ['{{}}'],
            'digit' => ['{{1}}'],
            'space before var name' => ['{{ var foo}}'],
        ];
    }

    public function testSimpleDirectiveProcessesMatchingConstruction(): void
    {
        $text = '{{unknownlegacy foo="bar"}}';
        preg_match($this->legacyDirective->getRegularExpression(), $text, $construction);
        $this->simpleDirective->expects($this->once())
            ->method('process')
            ->with($this->callback(fn (array $simple) => $simple['directiveName'] === 'unknownlegacy'))
            ->willReturn('processed');

        $this->assertSame('processed', $this->legacyDirective->process($construction, $this->filter, []));
    }
}
