<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Console\Command;

use Magento\Developer\Model\Setup\Declaration\Schema\WhitelistGenerator;
use Magento\Framework\Config\FileResolverByModule;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command that allows to generate whitelist, that will be used, when declaration data is installed.
 *
 * If whitelist already exists, new values will be added to existing whitelist.
 */
class TablesWhitelistGenerateCommand extends Command
{
    /**
     * Module name key, that will be used in whitelist generate command.
     */
    const MODULE_NAME_KEY = 'module-name';

    /**
     * @var WhitelistGenerator
     */
    private $whitelistGenerator;

    /**
     * @param WhitelistGenerator $whitelistGenerator
     * @param string|null $name
     */
    public function __construct(
        WhitelistGenerator $whitelistGenerator,
        $name = null
    ) {
        $this->whitelistGenerator = $whitelistGenerator;
        parent::__construct($name);
    }

    /**
     * Initialization of the command.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('setup:db-declaration:generate-whitelist')
            ->setDescription(
                'Generate whitelist of tables and columns that are allowed to be edited by declaration installer'
            )
            ->setDefinition(
                [
                    new InputOption(
                        self::MODULE_NAME_KEY,
                        null,
                        InputOption::VALUE_OPTIONAL,
                        'Name of the module where whitelist will be generated',
                        FileResolverByModule::ALL_MODULES
                    )
                ]
            );
        parent::configure();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $moduleName = $input->getOption(self::MODULE_NAME_KEY);

        try {
            $this->whitelistGenerator->generate($moduleName);
        } catch (ConfigurationMismatchException $e) {
            $output->writeln($e->getMessage());
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        } catch (\Exception $e) {
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
