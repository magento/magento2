<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Indexer\Console\Command\IndexerReindexCommand;
use Magento\Setup\Fixtures\FixtureModel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

class FixtureModelTest extends TestCase
{
    /**
     * @var FixtureModel
     */
    private $model;

    public function setUp(): void
    {
        $reindexCommandMock = $this->createMock(IndexerReindexCommand::class);
        $this->model = new FixtureModel($reindexCommandMock);
    }

    public function testReindex()
    {
        $outputMock = $this->createMock(OutputInterface::class);
        $this->model->reindex($outputMock);
    }
}
