<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Console\Command;

use \Magento\Framework\Mview;
use Magento\Indexer\Console\Command\IndexerStatusMviewCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\TableHelper;
use Magento\Store\Model\Website;
use Magento\Framework\Console\Cli;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexerStatusMviewCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IndexerStatusMviewCommand
     */
    private $command;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Mview\View\Collection
     */
    private $collection;

    protected function setUp()
    {
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        /** @var \Magento\Framework\Mview\View\Collection $collection */
        $this->collection = $this->objectManager->getObject(Mview\View\Collection::class);

        $reflectedCollection = new \ReflectionObject($this->collection);
        $isLoadedProperty = $reflectedCollection->getProperty('_isCollectionLoaded');
        $isLoadedProperty->setAccessible(true);
        $isLoadedProperty->setValue($this->collection, true);

        $this->command = $this->objectManager->getObject(
            IndexerStatusMviewCommand::class,
            ['collection' => $this->collection]
        );

        /** @var HelperSet $helperSet */
        $helperSet = $this->objectManager->getObject(
            HelperSet::class,
            ['helpers' => [$this->objectManager->getObject(TableHelper::class)]]
        );

        //Inject table helper for output
        $this->command->setHelperSet($helperSet);
    }

    public function testExecute()
    {
        $mviews = [
            [
                'view' => [
                    'view_id' => 'catalog_category_product',
                    'mode' => 'enabled',
                    'status' => 'idle',
                    'updated' => '2017-01-01 11:11:11',
                    'version_id' => 100,
                ],
                'changelog' => [
                    'version_id' => 110
                ],
            ],
            [
                'view' => [
                    'view_id' => 'catalog_product_category',
                    'mode' => 'disabled',
                    'status' => 'idle',
                    'updated' => '2017-01-01 11:11:11',
                    'version_id' => 100,
                ],
                'changelog' => [
                    'version_id' => 200
                ],
            ],
            [
                'view' => [
                    'view_id' => 'catalog_product_attribute',
                    'mode' => 'enabled',
                    'status' => 'idle',
                    'updated' => '2017-01-01 11:11:11',
                    'version_id' => 100,
                ],
                'changelog' => [
                    'version_id' => 100
                ],
            ],
        ];

        foreach ($mviews as $data) {
            $this->collection->addItem($this->generateMviewStub($data['view'], $data['changelog']));
        }
        $this->collection->addItem($this->getNeverEnabledMviewIndexerWithNoTable());

        $tester = new CommandTester($this->command);
        $this->assertEquals(Cli::RETURN_SUCCESS, $tester->execute([]));

        $linesOutput = array_filter(explode(PHP_EOL, $tester->getDisplay()));
        $this->assertCount(7, $linesOutput, 'There should be 7 lines output. 3 Spacers, 1 header, 3 content.');
        $this->assertEquals($linesOutput[0], $linesOutput[2], "Lines 0, 2, 7 should be spacer lines");
        $this->assertEquals($linesOutput[2], $linesOutput[6], "Lines 0, 2, 6 should be spacer lines");

        $headerValues = array_values(array_filter(explode('|', $linesOutput[1])));
        $this->assertEquals('ID', trim($headerValues[0]));
        $this->assertEquals('Mode', trim($headerValues[1]));
        $this->assertEquals('Status', trim($headerValues[2]));
        $this->assertEquals('Updated', trim($headerValues[3]));
        $this->assertEquals('Version ID', trim($headerValues[4]));
        $this->assertEquals('Backlog', trim($headerValues[5]));

        $catalogCategoryProductMviewData = array_values(array_filter(explode('|', $linesOutput[3])));
        $this->assertEquals('catalog_category_product', trim($catalogCategoryProductMviewData[0]));
        $this->assertEquals('enabled', trim($catalogCategoryProductMviewData[1]));
        $this->assertEquals('idle', trim($catalogCategoryProductMviewData[2]));
        $this->assertEquals('2017-01-01 11:11:11', trim($catalogCategoryProductMviewData[3]));
        $this->assertEquals('100', trim($catalogCategoryProductMviewData[4]));
        $this->assertEquals('10', trim($catalogCategoryProductMviewData[5]));
        unset($catalogCategoryProductMviewData);

        $catalogProductAttributeMviewData = array_values(array_filter(explode('|', $linesOutput[4])));
        $this->assertEquals('catalog_product_attribute', trim($catalogProductAttributeMviewData[0]));
        $this->assertEquals('enabled', trim($catalogProductAttributeMviewData[1]));
        $this->assertEquals('idle', trim($catalogProductAttributeMviewData[2]));
        $this->assertEquals('2017-01-01 11:11:11', trim($catalogProductAttributeMviewData[3]));
        $this->assertEquals('100', trim($catalogProductAttributeMviewData[4]));
        $this->assertEquals('0', trim($catalogProductAttributeMviewData[5]));
        unset($catalogProductAttributeMviewData);

        $catalogCategoryProductMviewData = array_values(array_filter(explode('|', $linesOutput[5])));
        $this->assertEquals('catalog_product_category', trim($catalogCategoryProductMviewData[0]));
        $this->assertEquals('disabled', trim($catalogCategoryProductMviewData[1]));
        $this->assertEquals('idle', trim($catalogCategoryProductMviewData[2]));
        $this->assertEquals('2017-01-01 11:11:11', trim($catalogCategoryProductMviewData[3]));
        $this->assertEquals('100', trim($catalogCategoryProductMviewData[4]));
        $this->assertEquals('100', trim($catalogCategoryProductMviewData[5]));
        unset($catalogCategoryProductMviewData);
    }

    /**
     * @param array $viewData
     * @param array $changelogData
     * @return Mview\View|Mview\View\Changelog|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function generateMviewStub(array $viewData, array $changelogData)
    {
        /** @var Mview\View\Changelog|\PHPUnit_Framework_MockObject_MockObject $stub */
        $changelog = $this->getMockBuilder(\Magento\Framework\Mview\View\Changelog::class)
            ->disableOriginalConstructor()
            ->getMock();

        $list = [];
        if ($changelogData['version_id'] !== $viewData['version_id']) {
            $list = range($viewData['version_id']+1, $changelogData['version_id']);
        }

        $changelog->expects($this->any())
            ->method('getList')
            ->willReturn($list);

        $changelog->expects($this->any())
            ->method('getVersion')
            ->willReturn($changelogData['version_id']);

        /** @var Mview\View|\PHPUnit_Framework_MockObject_MockObject $stub */
        $stub = $this->getMockBuilder(\Magento\Framework\Mview\View::class)
            ->disableOriginalConstructor()
            ->setMethods(['getChangelog', 'getState'])
            ->getMock();

        $stub->expects($this->any())
            ->method('getChangelog')
            ->willReturn($changelog);

        $stub->expects($this->any())
            ->method('getState')
            ->willReturnSelf();

        $stub->setData($viewData);

        return $stub;
    }

    /**
     * @return Mview\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getNeverEnabledMviewIndexerWithNoTable()
    {
        /** @var Mview\View\Changelog|\PHPUnit_Framework_MockObject_MockObject $stub */
        $changelog = $this->getMockBuilder(\Magento\Framework\Mview\View\Changelog::class)
            ->disableOriginalConstructor()
            ->getMock();

        $changelog->expects($this->any())
            ->method('getVersion')
            ->willThrowException(
                new Mview\View\ChangelogTableNotExistsException(new \Magento\Framework\Phrase("Do not render"))
            );

        /** @var Mview\View|\PHPUnit_Framework_MockObject_MockObject $notInitiatedMview */
        $notInitiatedMview = $this->getMockBuilder(\Magento\Framework\Mview\View::class)
            ->disableOriginalConstructor()
            ->getMock();

        $notInitiatedMview->expects($this->any())
            ->method('getChangelog')
            ->willReturn($changelog);

        return $notInitiatedMview;
    }

    public function testExecuteExceptionNoVerbosity()
    {
        /** @var \Magento\Framework\Mview\View|\PHPUnit_Framework_MockObject_MockObject $stub */
        $stub = $this->getMockBuilder(Mview\View::class)
            ->disableOriginalConstructor()
            ->getMock();

        $stub->expects($this->any())
            ->method('getChangelog')
            ->willThrowException(new \Exception("Dummy test exception"));

        $this->collection->addItem($stub);

        $tester = new CommandTester($this->command);
        $this->assertEquals(Cli::RETURN_FAILURE, $tester->execute([]));
        $linesOutput = array_filter(explode(PHP_EOL, $tester->getDisplay()));
        $this->assertEquals('Dummy test exception', $linesOutput[0]);
    }
}
