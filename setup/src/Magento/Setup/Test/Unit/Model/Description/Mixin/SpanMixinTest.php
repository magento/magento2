<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model\Description\Mixin;

use Magento\Setup\Model\Description\Mixin\Helper\RandomWordSelector;
use Magento\Setup\Model\Description\Mixin\Helper\WordWrapper;
use Magento\Setup\Model\Description\Mixin\SpanMixin;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SpanMixinTest extends TestCase
{
    /**
     * @var SpanMixin
     */
    private $mixin;

    /**
     * @var MockObject|RandomWordSelector
     */
    private $randomWordSelectorMock;

    /**
     * @var MockObject|WordWrapper
     */
    private $wordWrapperMock;

    protected function setUp(): void
    {
        $this->randomWordSelectorMock =
            $this->createMock(RandomWordSelector::class);
        $this->wordWrapperMock = $this->createMock(WordWrapper::class);

        $this->mixin = new SpanMixin(
            $this->randomWordSelectorMock,
            $this->wordWrapperMock
        );
    }

    public function testEmptyApply()
    {
        $this->assertEquals('', $this->mixin->apply(''));
    }

    public function testApply()
    {
        $fixtureString = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        $fixtureStringResult = '<span>Lorem</span> ipsum <span>dolor</span> sit amet, consectetur adipiscing elit.';
        $randWordsFixture = ['Lorem', 'dolor'];

        $this->randomWordSelectorMock
            ->expects($this->once())
            ->method('getRandomWords')
            ->with($fixtureString, $this->greaterThan(0))
            ->willReturn($randWordsFixture);

        $this->wordWrapperMock
            ->expects($this->once())
            ->method('wrapWords')
            ->with($fixtureString, $randWordsFixture, '<span>%s</span>')
            ->willReturn($fixtureStringResult);

        $this->assertEquals($fixtureStringResult, $this->mixin->apply($fixtureString));
    }
}
