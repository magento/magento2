<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Test\Unit\Model\Config\Reader\Source\Dynamic;

use Magento\Framework\DataObject;
use Magento\Store\Model\Config\Reader\Source\Dynamic\Website as WebsiteSource;
use Magento\Framework\App\Config\Scope\Converter;
use Magento\Store\Model\ResourceModel\Config\Collection\ScopedFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\WebsiteFactory;
use Magento\Store\Model\Website;
use Magento\Store\Model\Config\Reader\Source\Dynamic\DefaultScope;

/**
 * Class WebsiteTest
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WebsiteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ScopedFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $collectionFactory;

    /**
     * @var Converter|\PHPUnit\Framework\MockObject\MockObject
     */
    private $converter;

    /**
     * @var WebsiteFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $websiteFactory;

    /**
     * @var Website|\PHPUnit\Framework\MockObject\MockObject
     */
    private $website;

    /**
     * @var DefaultScope|\PHPUnit\Framework\MockObject\MockObject
     */
    private $defaultScopeReader;

    /**
     * @var WebsiteSource
     */
    private $websiteSource;

    protected function setUp(): void
    {
        $this->collectionFactory = $this->getMockBuilder(ScopedFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->converter = $this->getMockBuilder(Converter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteFactory = $this->getMockBuilder(\Magento\Store\Model\WebsiteFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();
        $this->website = $this->getMockBuilder(\Magento\Store\Model\Website::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->defaultScopeReader = $this->getMockBuilder(DefaultScope::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteSource = new WebsiteSource(
            $this->collectionFactory,
            $this->converter,
            $this->websiteFactory,
            $this->defaultScopeReader
        );
    }

    public function testGet()
    {
        $scopeCode = 'myWebsite';
        $expectedResult = [
            'config/key1' => 'default_db_value1',
            'config/key3' => 'default_db_value3',
        ];
        $this->websiteFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->website);
        $this->website->expects($this->once())
            ->method('load')
            ->with($scopeCode);
        $this->website->expects($this->once())
            ->method('getId')
            ->willReturn(1);
        $this->collectionFactory->expects($this->once())
            ->method('create')
            ->with(['scope' => ScopeInterface::SCOPE_WEBSITES, 'scopeId' => 1])
            ->willReturn([
                new DataObject(['path' => 'config/key1', 'value' => 'default_db_value1']),
                new DataObject(['path' => 'config/key3', 'value' => 'default_db_value3']),
            ]);
        $this->defaultScopeReader->expects($this->once())
            ->method('get')
            ->willReturn([]);
        $this->converter->expects($this->once())
            ->method('convert')
            ->with($expectedResult)
            ->willReturnArgument(0);
        $this->assertEquals($expectedResult, $this->websiteSource->get($scopeCode));
    }
}
