<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Console\Command\App;

use Magento\Deploy\Console\Command\App\SensitiveConfigSet\CollectorFactory;
use Magento\Deploy\Model\ConfigWriter;
use Magento\Framework\App\Config\CommentParserInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Scope\ValidatorInterface;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for set sensitive variable through deploy process.
 */
class SensitiveConfigSetCommand extends Command
{
    /**#@+
     * Names of input arguments or options.
     */
    const INPUT_OPTION_INTERACTIVE = 'interactive';
    const INPUT_OPTION_SCOPE = 'scope';
    const INPUT_OPTION_SCOPE_CODE = 'scope-code';
    const INPUT_ARGUMENT_PATH = 'path';
    const INPUT_ARGUMENT_VALUE = 'value';
    /**#@-*/

    /**
     * @var CommentParserInterface
     */
    private $commentParser;

    /**
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var ValidatorInterface
     */
    private $scopeValidator;

    /**
     * @var CollectorFactory
     */
    private $collectorFactory;

    /**
     * @param ConfigFilePool $configFilePool
     * @param CommentParserInterface $commentParser
     * @param ConfigWriter $configWriter
     * @param ValidatorInterface $scopeValidator
     * @param CollectorFactory $collectorFactory
     */
    public function __construct(
        ConfigFilePool $configFilePool,
        CommentParserInterface $commentParser,
        ConfigWriter $configWriter,
        ValidatorInterface $scopeValidator,
        CollectorFactory $collectorFactory
    ) {
        parent::__construct();
        $this->commentParser = $commentParser;
        $this->configFilePool = $configFilePool;
        $this->configWriter = $configWriter;
        $this->scopeValidator = $scopeValidator;
        $this->collectorFactory = $collectorFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument(
            self::INPUT_ARGUMENT_PATH,
            InputArgument::OPTIONAL,
            'Configuration path for example group/section/field_name'
        );
        $this->addArgument(
            self::INPUT_ARGUMENT_VALUE,
            InputArgument::OPTIONAL,
            'Configuration value'
        );
        $this->addOption(
            self::INPUT_OPTION_INTERACTIVE,
            'i',
            InputOption::VALUE_NONE,
            'Enable interactive mode to set all sensitive variables'
        );
        $this->addOption(
            self::INPUT_OPTION_SCOPE,
            null,
            InputOption::VALUE_OPTIONAL,
            'Scope for configuration, if not set use \'default\'',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );
        $this->addOption(
            self::INPUT_OPTION_SCOPE_CODE,
            null,
            InputOption::VALUE_OPTIONAL,
            'Scope code for configuration, empty string by default',
            ''
        );
        $this->setName('config:sensitive:set')
            ->setDescription('Set sensitive configuration values');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $scope = $input->getOption(self::INPUT_OPTION_SCOPE);
        $scopeCode = $input->getOption(self::INPUT_OPTION_SCOPE_CODE);

        try {
            $this->scopeValidator->isValid($scope, $scopeCode);
        } catch (LocalizedException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        $configFilePath = $this->configFilePool->getPathsByPool(ConfigFilePool::LOCAL)[ConfigFilePool::APP_CONFIG];
        try {
            $configPaths = $this->commentParser->execute($configFilePath);
        } catch (FileSystemException $e) {
            $output->writeln(
                sprintf(
                    '<error>%s</error>',
                    'File app/etc/' . $configFilePath . ' can\'t be read. '
                    . 'Please check if it exists and has read permissions.'
                )
            );
            return Cli::RETURN_FAILURE;
        }

        if (empty($configPaths)) {
            $output->writeln('<error>There are no sensitive configurations to fill</error>');
            return Cli::RETURN_FAILURE;
        }

        try {
            $isInteractive = $input->getOption(self::INPUT_OPTION_INTERACTIVE);
            $collector = $this->collectorFactory->create(
                $isInteractive ? CollectorFactory::TYPE_INTERACTIVE : CollectorFactory::TYPE_SIMPLE
            );
            $values = $collector->getValues($input, $output, $configPaths);
            $this->configWriter->save($values, $scope, $scopeCode);
        } catch (LocalizedException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            return Cli::RETURN_FAILURE;
        }

        $this->writeSuccessMessage($output, $isInteractive);

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Writes success message.
     *
     * @param OutputInterface $output
     * @param boolean $isInteractive
     * @return void
     */
    private function writeSuccessMessage(OutputInterface $output, $isInteractive)
    {
        $output->writeln(sprintf(
            '<info>Configuration value%s saved in app/etc/%s</info>',
            $isInteractive ? 's' : '',
            $this->configFilePool->getPath(ConfigFilePool::APP_CONFIG)
        ));
    }
}
