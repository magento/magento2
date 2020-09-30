<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Console\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @var WriteFactory
     */
    private $writeFactory;

    /**
     * GeneratePatchCommand constructor.
     *
     * @param ComponentRegistrar $componentRegistrar
     * @param DirectoryList $directoryList
     * @param ReadFactory $readFactory
     * @param WriteFactory $writeFactory
     */
    public function __construct(
        ComponentRegistrar $componentRegistrar,
        DirectoryList $directoryList,
        ReadFactory $readFactory,
        WriteFactory $writeFactory
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->directoryList = $directoryList;
        $this->readFactory = $readFactory;
        $this->writeFactory = $writeFactory;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_NAME)
            ->setDescription('Generate patch and put it in specific folder.')
            ->setDefinition(
                [
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
                        'Find out what type of patch should be generated. Available values: `data`, `schema`.',
                        'data'
                    ),
                ]
            );

        parent::configure();
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $moduleName = $input->getArgument(self::MODULE_NAME);
        $patchName = $input->getArgument(self::INPUT_KEY_PATCH_NAME);
        $includeRevertMethod = false;
        if ($input->getOption(self::INPUT_KEY_IS_REVERTABLE)) {
            $includeRevertMethod = true;
        }
        $type = $input->getOption(self::INPUT_KEY_PATCH_TYPE);
        $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        if (null === $modulePath) {
            throw new \InvalidArgumentException(sprintf('Cannot find a registered module with name "%s"', $moduleName));
        }
        $preparedModuleName = str_replace('_', '\\', $moduleName);
        $preparedType = ucfirst($type);
        $patchInterface = sprintf('%sPatchInterface', $preparedType);
        $patchTemplateData = $this->getPatchTemplate();
        $patchTemplateData = str_replace('%moduleName%', $preparedModuleName, $patchTemplateData);
        $patchTemplateData = str_replace('%patchType%', $preparedType, $patchTemplateData);
        $patchTemplateData = str_replace('%class%', $patchName, $patchTemplateData);

        $tplUseSchemaPatchInt = '%SchemaPatchInterface%';
        $tplUseDataPatchInt = '%useDataPatchInterface%';
        $valUseSchemaPatchInt = 'use Magento\Framework\Setup\Patch\SchemaPatchInterface;' . "\n";
        $valUseDataPatchInt = 'use Magento\Framework\Setup\Patch\DataPatchInterface;' . "\n";
        if ($type === 'schema') {
            $patchTemplateData = str_replace($tplUseSchemaPatchInt, $valUseSchemaPatchInt, $patchTemplateData);
            $patchTemplateData = str_replace($tplUseDataPatchInt, '', $patchTemplateData);
        } else {
            $patchTemplateData = str_replace($tplUseDataPatchInt, $valUseDataPatchInt, $patchTemplateData);
            $patchTemplateData = str_replace($tplUseSchemaPatchInt, '', $patchTemplateData);
        }

        $tplUsePatchRevertInt = '%usePatchRevertableInterface%';
        $tplImplementsInt = '%implementsInterfaces%';
        $tplRevertFunction = '%revertFunction%';
        $valUsePatchRevertInt = 'use Magento\Framework\Setup\Patch\PatchRevertableInterface;' . "\n";

        if ($includeRevertMethod) {
            $valImplementsInt = <<<BOF

    $patchInterface,
    PatchRevertableInterface
BOF;
            $patchTemplateData = str_replace($tplUsePatchRevertInt, $valUsePatchRevertInt, $patchTemplateData);
            $patchTemplateData = str_replace(' ' . $tplImplementsInt, $valImplementsInt, $patchTemplateData);
            $patchTemplateData = str_replace($tplRevertFunction, $this->getRevertMethodTemplate(), $patchTemplateData);
        } else {
            $patchTemplateData = str_replace($tplUsePatchRevertInt, '', $patchTemplateData);
            $patchTemplateData = str_replace($tplImplementsInt, $patchInterface, $patchTemplateData);
            $patchTemplateData = str_replace($tplRevertFunction, '', $patchTemplateData);
        }

        $patchDir = $modulePath . '/Setup/Patch/' . $preparedType;
        $patchFile = $patchName . '.php';

        $fileWriter = $this->writeFactory->create($patchDir);
        $fileWriter->writeFile($patchFile, $patchTemplateData);

        $outputPatchFile = str_replace($this->directoryList->getRoot() . '/', '', $patchDir . '/' . $patchFile);
        $output->writeln(__('Patch %1 has been successfully generated.', $outputPatchFile));

        return Cli::RETURN_SUCCESS;
    }

    /**
     * Returns patch template
     *
     * @return string
     * @throws FileSystemException
     */
    private function getPatchTemplate(): string
    {
        $read = $this->readFactory->create(__DIR__ . '/');
        return $read->readFile('patch_template.php.dist');
    }

    /**
     * Returns template of revert() function
     *
     * @return string
     * @throws FileSystemException
     */
    private function getRevertMethodTemplate(): string
    {
        $read = $this->readFactory->create(__DIR__ . '/');
        return $read->readFile('template_revert_function.php.dist');
    }
}
