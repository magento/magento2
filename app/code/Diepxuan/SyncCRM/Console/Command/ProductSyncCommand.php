<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-03-31 21:48:09
 */

namespace Diepxuan\SyncCRM\Console\Command;

use Diepxuan\SyncCRM\Sync\ProductSync as Sync;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductSyncCommand extends Command
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    protected Sync $sync;

    public function __construct(
        Sync $sync
    ) {
        $this->sync = $sync;
        parent::__construct();
    }

    /**
     * Executes the current command.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return null|int 0 if everything went fine, or an error code
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        $this->sync->sync();

        return Command::SUCCESS;
    }

    /**
     * Console output.
     *
     * @param mixed $str
     */
    public function output(string $str): void
    {
        $this->output->writeln($str);
    }

    /**
     * Init command.
     */
    protected function configure(): void
    {
        $this
            ->setName('crm:sync:product')
            ->setDescription('Sync products with CRM.')
        ;
        parent::configure();
    }
}
