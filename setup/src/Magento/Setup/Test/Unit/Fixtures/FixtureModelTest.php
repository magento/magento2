<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Setup\Fixtures\FixtureModel;

class FixtureModelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Setup\Fixtures\FixtureModel
     */
    private $model;

    public function setUp()
    {
        $reindexCommandMock = $this->createMock(\Magento\Indexer\Console\Command\IndexerReindexCommand::class);
        $this->model = new FixtureModel($reindexCommandMock);
    }

    public function testReindex()
    {
        $outputMock = $this->createMock(\Symfony\Component\Console\Output\OutputInterface::class);
        $this->model->reindex($outputMock);
    }
}
