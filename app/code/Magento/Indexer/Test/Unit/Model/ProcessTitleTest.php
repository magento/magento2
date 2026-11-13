<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Model;

use Laminas\Http\PhpEnvironment\Request as Environment;
use Magento\Indexer\Model\ProcessTitle;
use PHPUnit\Framework\Attributes\RequiresFunction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class covers process title management test logic
 */
#[RequiresFunction('cli_set_process_title')]
class ProcessTitleTest extends TestCase
{
    private const COMMAND_LINE = 'bin/magento indexer:reindex';

    private const CRON_TITLE = 'php bin/magento cron:run --group=index # job: indexer_reindex_all_invalid';

    /**
     * @var Environment|MockObject
     */
    private $environmentMock;

    /**
     * @var ProcessTitle
     */
    private ProcessTitle $model;

    /**
     * @var string|null
     */
    private ?string $currentTitle;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->currentTitle = cli_get_process_title();
        $this->environmentMock = $this->createMock(Environment::class);
        $this->environmentMock->method('getServer')
            ->with('argv', [])
            ->willReturn(explode(' ', self::COMMAND_LINE));
        $this->model = new ProcessTitle($this->environmentMock);
        cli_set_process_title('');
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->currentTitle !== null) {
            cli_set_process_title($this->currentTitle);
        }
    }

    /**
     * @return void
     */
    public function testSetIndexerContextUsesCommandLineWhenTitleIsNotSet(): void
    {
        $this->model->setIndexerContext('catalog_product_price');

        $this->assertEquals(
            PHP_BINARY . ' ' . self::COMMAND_LINE . ' # indexer: catalog_product_price',
            cli_get_process_title()
        );
    }

    /**
     * @return void
     */
    public function testSetIndexerContextAddsContextToTitleWithContext(): void
    {
        cli_set_process_title(self::CRON_TITLE);

        $this->model->setIndexerContext('catalog_product_price');

        $this->assertEquals(self::CRON_TITLE . ', indexer: catalog_product_price', cli_get_process_title());
    }

    /**
     * @return void
     */
    public function testSetIndexerContextReplacesPreviousIndexer(): void
    {
        cli_set_process_title(self::COMMAND_LINE);

        $this->model->setIndexerContext('catalog_product_price');
        $this->model->setIndexerContext('catalogsearch_fulltext');

        $this->assertEquals(self::COMMAND_LINE . ' # indexer: catalogsearch_fulltext', cli_get_process_title());
    }

    /**
     * @return void
     */
    public function testSetProcessContext(): void
    {
        cli_set_process_title(self::COMMAND_LINE);

        $this->model->setProcessContext(2);

        $this->assertEquals(self::COMMAND_LINE . ' # process: 2', cli_get_process_title());
    }

    /**
     * @return void
     */
    public function testSetProcessContextKeepsIndexerContext(): void
    {
        cli_set_process_title(self::CRON_TITLE);

        $this->model->setIndexerContext('catalog_product_price');
        $this->model->setProcessContext(2);

        $this->assertEquals(
            self::CRON_TITLE . ', indexer: catalog_product_price, process: 2',
            cli_get_process_title()
        );
    }

    /**
     * @return void
     */
    public function testSetProcessContextKeepsTitleSetByAnotherInstance(): void
    {
        cli_set_process_title(self::CRON_TITLE);
        $this->model->setIndexerContext('catalog_product_price');

        $anotherModel = new ProcessTitle($this->environmentMock);
        $anotherModel->setProcessContext(1);

        $this->assertEquals(
            self::CRON_TITLE . ', indexer: catalog_product_price, process: 1',
            cli_get_process_title()
        );
    }

    /**
     * @return void
     */
    public function testSetContextReplacesValueInPlace(): void
    {
        cli_set_process_title(self::COMMAND_LINE);

        $this->model->setContext('batch', 'first');
        $this->model->setProcessContext(1);
        $this->model->setContext('batch', 'second');

        $this->assertEquals(self::COMMAND_LINE . ' # batch: second, process: 1', cli_get_process_title());
    }

    /**
     * @return void
     */
    public function testRestore(): void
    {
        cli_set_process_title(self::CRON_TITLE);

        $this->model->setIndexerContext('catalog_product_price');
        $this->model->setProcessContext(1);
        $this->model->restore();

        $this->assertEquals(self::CRON_TITLE, cli_get_process_title());
    }

    /**
     * @return void
     */
    public function testRestoreWithoutContextSet(): void
    {
        cli_set_process_title(self::CRON_TITLE);

        $this->model->restore();

        $this->assertEquals(self::CRON_TITLE, cli_get_process_title());
    }

    /**
     * @return void
     */
    public function testSetContextAfterRestore(): void
    {
        cli_set_process_title(self::CRON_TITLE);
        $this->model->setIndexerContext('catalog_product_price');
        $this->model->setProcessContext(1);
        $this->model->restore();
        cli_set_process_title(self::COMMAND_LINE);

        $this->model->setIndexerContext('catalogsearch_fulltext');

        $this->assertEquals(self::COMMAND_LINE . ' # indexer: catalogsearch_fulltext', cli_get_process_title());
    }
}
