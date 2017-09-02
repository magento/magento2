<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model;

class SearchTermDescriptionGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Model\SearchTermDescriptionGenerator
     */
    private $searchTermDescriptionGenerator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\Description\DescriptionGenerator
     */
    private $descriptionGeneratorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Model\SearchTermManager
     */
    private $searchTermManagerMock;

    public function setUp()
    {
        $this->descriptionGeneratorMock = $this->getMock(
            \Magento\Setup\Model\Description\DescriptionGenerator::class,
            [],
            [],
            '',
            false
        );
        $this->searchTermManagerMock = $this->getMock(
            \Magento\Setup\Model\SearchTermManager::class,
            [],
            [],
            '',
            false
        );

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
