<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Setup\Fixtures\FixtureModel;

class FixtureModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Fixtures\FixtureModel
     */
    private $model;

    public function setUp()
    {
        $reindexCommandMock = $this->getMock(
            \Magento\Indexer\Console\Command\IndexerReindexCommand::class,
            [],
            [],
            '',
            false
        );
        $this->model = new FixtureModel($reindexCommandMock);
    }

    public function testReindex()
    {
        $outputMock = $this->getMock(\Symfony\Component\Console\Output\OutputInterface::class, [], [], '', false);
        $this->model->reindex($outputMock);
    }
}
