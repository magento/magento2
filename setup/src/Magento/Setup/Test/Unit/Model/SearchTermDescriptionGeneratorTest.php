<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

class SearchTermDescriptionGeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Model\SearchTermDescriptionGenerator
     */
    private $searchTermDescriptionGenerator;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Setup\Model\Description\DescriptionGenerator
     */
    private $descriptionGeneratorMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Setup\Model\SearchTermManager
     */
    private $searchTermManagerMock;

    protected function setUp(): void
    {
        $this->descriptionGeneratorMock =
            $this->createMock(\Magento\Setup\Model\Description\DescriptionGenerator::class);
        $this->searchTermManagerMock = $this->createMock(\Magento\Setup\Model\SearchTermManager::class);

        $this->searchTermDescriptionGenerator = new \Magento\Setup\Model\SearchTermDescriptionGenerator(
            $this->descriptionGeneratorMock,
            $this->searchTermManagerMock
        );
    }

    public function testGeneratorWithCaching()
    {
        $descriptionMock = '<o>';
        $firstProductIndex = 1;
        $secondProductIndex = 2;

        $this->descriptionGeneratorMock
            ->expects($this->once())
            ->method('generate')
            ->willReturn($descriptionMock);

        $this->searchTermManagerMock
            ->expects($this->exactly(2))
            ->method('applySearchTermsToDescription')
            ->withConsecutive(
                [$descriptionMock, $firstProductIndex],
                [$descriptionMock, $secondProductIndex]
            );

        $this->searchTermDescriptionGenerator->generate($firstProductIndex);
        $this->searchTermDescriptionGenerator->generate($secondProductIndex);
    }
}
