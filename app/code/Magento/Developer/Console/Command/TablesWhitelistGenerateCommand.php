<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Developer\Console\Command;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Config\FileResolverByModule;
use Magento\Framework\Module\Dir;
use Magento\Framework\Setup\Declaration\Schema\Diff\Diff;
use Magento\Framework\Setup\JsonPersistor;
use Magento\Framework\Setup\Declaration\Schema\Declaration\ReaderComposite;
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
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * @var ReaderComposite
     */
    private $readerComposite;

    /**
     * @var JsonPersistor
     */
    private $jsonPersistor;

    /**
     * @var array
     */
    private $primaryDbSchema;

    /**
     * @param ComponentRegistrar $componentRegistrar
     * @param ReaderComposite $readerComposite
     * @param JsonPersistor $jsonPersistor
     * @param string|null $name
     */
    public function __construct(
        ComponentRegistrar $componentRegistrar,
        ReaderComposite $readerComposite,
        JsonPersistor $jsonPersistor,
        $name = null
    ) {
        parent::__construct($name);
        $this->componentRegistrar = $componentRegistrar;
        $this->readerComposite = $readerComposite;
        $this->jsonPersistor = $jsonPersistor;
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
     * Update whitelist tables for all modules that are enabled on the moment.
     *
     * @param string $moduleName
     * @return void
     */
    private function persistModule($moduleName)
    {
        $content = [];
        $modulePath = $this->componentRegistrar->getPath('module', $moduleName);
        $whiteListFileName = $modulePath
            . DIRECTORY_SEPARATOR
            . Dir::MODULE_ETC_DIR
            . DIRECTORY_SEPARATOR
            . Diff::GENERATED_WHITELIST_FILE_NAME;
        //We need to load whitelist file and update it with new revision of code.
        if (file_exists($whiteListFileName)) {
            $content = json_decode(file_get_contents($whiteListFileName), true);
        }

        $newContent = $this->filterPrimaryTables($this->readerComposite->read($moduleName));

        //Do merge between what we have before, and what we have now and filter to only certain attributes.
        $content = array_replace_recursive(
            $content,
            $this->filterAttributeNames($newContent)
        );
        if (!empty($content)) {
            $this->jsonPersistor->persist($content, $whiteListFileName);
        }
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $moduleName = $input->getOption(self::MODULE_NAME_KEY);

        try {
            if ($moduleName === FileResolverByModule::ALL_MODULES) {
                foreach (array_keys($this->componentRegistrar->getPaths('module')) as $moduleName) {
                    $this->persistModule($moduleName);
                }
            } else {
                $this->persistModule($moduleName);
            }
        } catch (\Exception $e) {
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        //If script comes here, that we sucessfully write whitelist configuration
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    /**
     * Filter attribute names
     *
     * As for whitelist we do not need any specific attributes like nullable or indexType, we need to choose only names.
     *
     * @param array $content
     * @return array
     */
    private function filterAttributeNames(array $content) : array
    {
        $names = [];
        $types = ['column', 'index', 'constraint'];

        foreach ($content['table'] as $tableName => $tableContent) {
            foreach ($types as $type) {
                if (isset($tableContent[$type])) {
                    //Add elements to whitelist
                    foreach (array_keys($tableContent[$type]) as $elementName) {
                        //Depends on flag column will be whitelisted or not
                        $names[$tableName][$type][$elementName] = true;
                    }
                }
            }
        }

        return $names;
    }

    /**
     * Load db_schema content from the primary scope app/etc/db_schema.xml.
     *
     * @return array
     */
    private function getPrimaryDbSchema()
    {
        if (!$this->primaryDbSchema) {
            $this->primaryDbSchema = $this->readerComposite->read('primary');
        }
        return $this->primaryDbSchema;
    }

    /**
     * Filter tables from module db_schema.xml as they should not contain the primary system tables.
     *
     * @param array $moduleDbSchema
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    private function filterPrimaryTables(array $moduleDbSchema)
    {
        $primaryDbSchema = $this->getPrimaryDbSchema();
        if (isset($moduleDbSchema['table']) && isset($primaryDbSchema['table'])) {
            foreach ($primaryDbSchema['table'] as $tableNameKey => $tableContents) {
                unset($moduleDbSchema['table'][$tableNameKey]);
            }
        }
        return $moduleDbSchema;
    }
}
