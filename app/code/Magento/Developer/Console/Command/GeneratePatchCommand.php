<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Console\Command;

use Magento\Developer\Model\Di\Information;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Helper\Table;

/**
 * Allows to generate setup patches
 */
class GeneratePatchCommand extends Command
{
    /**
     * Command arguments and options
     */
    const COMMAND_NAME = 'setup:db-declaration:generate-patch';
    const MODULE_NAME = 'module';
    const INPUT_KEY_IS_REVERTABLE = 'revertable';
    const INPUT_KEY_PATCH_TYPE = 'type';
    const INPUT_KEY_PATCH_NAME = 'patch';

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * GeneratePatchCommand constructor.
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(ComponentRegistrar $componentRegistrar)
    {
        $this->componentRegistrar = $componentRegistrar;
        parent::__construct();
    }

    /**
     * Configure command
     *
     * @inheritdoc
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Generate patch and put it in specific folder.')
            ->setDefinition([
                new InputArgument(
                    self::MODULE_NAME,
                    InputArgument::REQUIRED,
                    'Module name'
                ),
                new InputArgument(
                    self::INPUT_KEY_PATCH_NAME,
                    InputArgument::REQUIRED,
                    'Patch name'
                ),
                new InputOption(
                    self::INPUT_KEY_IS_REVERTABLE,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Check whether patch is revertable or not.',
                    false
                ),
                new InputOption(
                    self::INPUT_KEY_PATCH_TYPE,
                    null,
                    InputOption::VALUE_OPTIONAL,
                    'Find out what type of patch should be generated.',
                    'data'
                ),
            ]);

        parent::configure();
    }

    /**
     * Patch template
     *
     * @return string
     */
    private function getPatchTemplate() : string
    {
        return file_get_contents(__DIR__ . '/patch_template.php.dist');
    }

    /**
     * @inheritdoc
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $moduleName = $input->getArgument(self::MODULE_NAME);
        $patchName = $input->getArgument(self::INPUT_KEY_PATCH_NAME);
        $type = $input->getOption(self::INPUT_KEY_PATCH_TYPE);
        $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        $preparedModuleName = str_replace('_', '\\', $moduleName);
        $preparedType = ucfirst($type);
        $patchInterface = sprintf('%sPatchInterface', $preparedType);
        $patchTemplateData = $this->getPatchTemplate();
        $patchTemplateData = str_replace('%moduleName%', $preparedModuleName, $patchTemplateData);
        $patchTemplateData = str_replace('%patchType%', $preparedType, $patchTemplateData);
        $patchTemplateData = str_replace('%patchInterface%', $patchInterface, $patchTemplateData);
        $patchTemplateData = str_replace('%class%', $patchName, $patchTemplateData);
        $patchDir = $patchToFile = $modulePath . '/Setup/Patch/' . $preparedType;

        if (!is_dir($patchDir)) {
            mkdir($patchDir, 0777, true);
        }
        $patchToFile = $patchDir . '/' . $patchName . '.php';
        file_put_contents($patchToFile, $patchTemplateData);
        return Cli::RETURN_SUCCESS;
    }
}
