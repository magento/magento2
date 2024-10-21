<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Command;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Magento\Framework\Module\ModuleList;
use Magento\Setup\Model\ConfigModel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ConfigSetCommand extends AbstractSetupCommand
{
    public const NAME = 'setup:config:set';
    /**
     * @var ConfigModel
     */
    protected $configModel;

    /**
     * Enabled module list
     *
     * @var ModuleList
     */
    private $moduleList;

    /**
     * Existing deployment config
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Constructor
     *
     * @param \Magento\Setup\Model\ConfigModel $configModel
     * @param ModuleList $moduleList
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        ConfigModel $configModel,
        ModuleList $moduleList,
        DeploymentConfig $deploymentConfig
    ) {
        $this->configModel = $configModel;
        $this->moduleList = $moduleList;
        $this->deploymentConfig = $deploymentConfig;
        parent::__construct();
    }

    /**
     * Initialization of the command
     *
     * @return void
     */
    protected function configure()
    {
        $options = $this->configModel->getAvailableOptions();

        $this->setName(self::NAME)
            ->setDescription('Creates or modifies the deployment configuration')
            ->setDefinition($options);

        parent::configure();
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputOptions = $input->getOptions();
        $optionCollection = $this->configModel->getAvailableOptions();
        $commandOptions = [];
        $optionsWithDefaultValues = [];

        foreach ($optionCollection as $option) {
            $commandOptions[$option->getName()] = false;

            $currentValue = $this->deploymentConfig->get($option->getConfigPath());
            $needOverwrite = ($currentValue !== null) &&
                ($inputOptions[$option->getName()] !== null) &&
                ($inputOptions[$option->getName()] !== $currentValue);
            if ($needOverwrite) {
                $dialog = $this->getHelperSet()->get('question');
                $question = new Question(
                    '<question>Overwrite the existing configuration for ' . $option->getName() . '?[Y/n]</question>',
                    'y'
                );
                if (strtolower($dialog->ask($input, $output, $question)) !== 'y') {
                    $inputOptions[$option->getName()] = null;
                }
            }

            if ($option->getDefault() === $inputOptions[$option->getName()]
                && $inputOptions[$option->getName()] !== null
            ) {
                $optionsWithDefaultValues[] = $option->getName();
            }
        }

        $inputOptions = array_filter(
            $inputOptions,
            function ($value) {
                return $value !== null;
            }
        );

        $optionsToChange = array_intersect(array_keys($inputOptions), array_keys($commandOptions));
        $optionsToChange = array_diff($optionsToChange, [ConfigOptionsListConstants::INPUT_KEY_SKIP_DB_VALIDATION]);

        $this->configModel->process($inputOptions);

        $optionsWithDefaultValues = array_diff(
            $optionsWithDefaultValues,
            [ConfigOptionsListConstants::INPUT_KEY_SKIP_DB_VALIDATION]
        );

        if (count($optionsWithDefaultValues) > 0) {
            $defaultValuesMessage = implode(', ', $optionsWithDefaultValues);
            $output->writeln(
                '<info>We saved default values for these options: ' . $defaultValuesMessage . '.</info>'
            );
        } else {
            if (count($optionsToChange) > 0) {
                $output->writeln('<info>You saved the new configuration.</info>');
            } else {
                $output->writeln('<info>You made no changes to the configuration.</info>');
            }
        }
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $inputOptions = $input->getOptions();

        $errors = $this->configModel->validate($inputOptions);

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $output->writeln("<error>$error</error>");
            }
            throw new \InvalidArgumentException('Parameter validation failed');
        }
    }
}
