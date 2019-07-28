<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Description\Mixin;

class ItalicMixinTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Model\Description\Mixin\ItalicMixin
     */
    private $mixin;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Description\Mixin\Helper\RandomWordSelector
     */
    private $randomWordSelectorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Description\Mixin\Helper\WordWrapper
     */
    private $wordWrapperMock;

    public function setUp()
    {
        $this->randomWordSelectorMock =
            $this->createMock(\Magento\Setup\Model\Description\Mixin\Helper\RandomWordSelector::class);
        $this->wordWrapperMock = $this->createMock(\Magento\Setup\Model\Description\Mixin\Helper\WordWrapper::class);

        $this->mixin = new \Magento\Setup\Model\Description\Mixin\ItalicMixin(
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
        $fixtureStringResult = '<i>Lorem</i> ipsum <i>dolor</i> sit amet, consectetur adipiscing elit.';
        $randWordsFixture = ['Lorem', 'dolor'];

        $this->randomWordSelectorMock
            ->expects($this->once())
            ->method('getRandomWords')
            ->with($this->equalTo($fixtureString), $this->greaterThan(0))
            ->willReturn($randWordsFixture);

        $this->wordWrapperMock
            ->expects($this->once())
            ->method('wrapWords')
            ->with($fixtureString, $randWordsFixture, '<i>%s</i>')
            ->willReturn($fixtureStringResult);

        $this->assertEquals($fixtureStringResult, $this->mixin->apply($fixtureString));
    }
}
