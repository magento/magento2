<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Framework\App\Config\Storage\Writer as ConfigWriter;
use Magento\Setup\Fixtures\FixtureModel;
use Magento\Setup\Fixtures\TaxRulesFixture;
use Magento\Tax\Api\Data\TaxRateInterfaceFactory;
use Magento\Tax\Api\Data\TaxRuleInterfaceFactory;
use Magento\Tax\Api\TaxRateRepositoryInterface;
use Magento\Tax\Api\TaxRuleRepositoryInterface;
use Magento\Tax\Model\ResourceModel\Calculation\Rate\CollectionFactory;
use Magento\Tax\Model\ResourceModel\Calculation\Rate\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxRulesFixtureTest extends TestCase
{

    /**
     * @var MockObject|FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var TaxRulesFixture
     */
    private $model;

    /**
     * @var ConfigWriter
     */
    private $configWriterMock;

    /**
     * @var TaxRateInterfaceFactory
     */
    private $taxRateRepositoryMock;

    /**
     * @var
     */
    private $taxRateFactoryMock;

    /**
     * @var CollectionFactory
     */
    private $taxRateCollectionFactoryMock;

    /**
     * @var TaxRuleInterfaceFactory
     */
    private $taxRuleFactoryMock;

    /**
     * @var TaxRuleRepositoryInterface
     */
    private $taxRuleRepositoryMock;

    public function testExecute()
    {
        $this->fixtureModelMock = $this->getMockBuilder(FixtureModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxRateFactoryMock = $this->getMockBuilder(TaxRateInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxRateRepositoryMock = $this->getMockBuilder(TaxRateRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->configWriterMock = $this->getMockBuilder(ConfigWriter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxRuleFactoryMock = $this->getMockBuilder(TaxRuleInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->taxRuleRepositoryMock = $this->getMockBuilder(TaxRuleRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['save', 'get', 'delete', 'deleteById', 'getList'])
            ->getMockForAbstractClass();

        $this->fixtureModelMock
            ->expects($this->exactly(2))
            ->method('getValue')
            ->willReturnMap([
                ['tax_mode', 'VAT'],
                ['tax_rules', 2]
            ]);

        $this->taxRateCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $taxRateCollectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAllIds'])
            ->getMock();

        $this->taxRateCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($taxRateCollectionMock);

        $taxRateCollectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn([1]);

        $this->model = new TaxRulesFixture(
            $this->fixtureModelMock,
            $this->taxRuleRepositoryMock,
            $this->taxRuleFactoryMock,
            $this->taxRateCollectionFactoryMock,
            $this->taxRateFactoryMock,
            $this->taxRateRepositoryMock,
            $this->configWriterMock
        );

        $this->model->execute();
    }
}
