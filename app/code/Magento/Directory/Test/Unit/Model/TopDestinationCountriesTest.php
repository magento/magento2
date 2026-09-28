<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Directory\Test\Unit\Model;

use Magento\Directory\Model\TopDestinationCountries;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TopDestinationCountriesTest extends TestCase
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfigMock;

    /**
     * @var TopDestinationCountries
     */
    protected $model;

    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)
            ->getMock();
        $objectManager = new ObjectManager($this);
        $arguments = [
            'scopeConfig' => $this->scopeConfigMock
        ];
        $this->model = $objectManager
            ->getObject(TopDestinationCountries::class, $arguments);
    }

    #[DataProvider('toTestGetTopDestinationsDataProvider')]
    public function testGetTopDestinations($options, $expectedResults)
    {
        $this->scopeConfigMock->expects($this->once())->method('getValue')->willReturn($options);
        $this->assertEquals($expectedResults, $this->model->getTopDestinations());
    }

    /**
     * @return array
     */
    public static function toTestGetTopDestinationsDataProvider()
    {
        return [
            ['UA,AF', ['UA', 'AF']],
            ['', []]
        ];
    }
}
