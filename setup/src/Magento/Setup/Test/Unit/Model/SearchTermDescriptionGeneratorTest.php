<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\Description\DescriptionGenerator;
use Magento\Setup\Model\SearchTermDescriptionGenerator;
use Magento\Setup\Model\SearchTermManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SearchTermDescriptionGeneratorTest extends TestCase
{
    /**
     * @var SearchTermDescriptionGenerator
     */
    private $searchTermDescriptionGenerator;

    /**
     * @var MockObject|DescriptionGenerator
     */
    private $descriptionGeneratorMock;

    /**
     * @var MockObject|SearchTermManager
     */
    private $searchTermManagerMock;

    protected function setUp(): void
    {
        $this->descriptionGeneratorMock =
            $this->createMock(DescriptionGenerator::class);
        $this->searchTermManagerMock = $this->createMock(SearchTermManager::class);

        $this->searchTermDescriptionGenerator = new SearchTermDescriptionGenerator(
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
            ->willReturnCallback(
                function ($arg1, $arg2) use ($descriptionMock, $firstProductIndex, $secondProductIndex) {
                    if ($arg1 === $descriptionMock && $arg2 === $firstProductIndex) {
                        return null;
                    } elseif ($arg1 === $descriptionMock && $arg2 === $secondProductIndex) {
                        return null;
                    }
                }
            );

        $this->searchTermDescriptionGenerator->generate($firstProductIndex);
        $this->searchTermDescriptionGenerator->generate($secondProductIndex);
    }
}
