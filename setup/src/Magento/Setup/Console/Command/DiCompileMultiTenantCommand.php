<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\Api\Code\Generator\Mapper;
use Magento\Framework\Api\Code\Generator\SearchResults;
use Magento\Framework\Autoload\AutoloaderRegistry;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Interception\Code\Generator\Interceptor;
use Magento\Framework\ObjectManager\Code\Generator\Converter;
use Magento\Framework\ObjectManager\Code\Generator\Factory;
use Magento\Framework\ObjectManager\Code\Generator\Proxy;
use Magento\Framework\ObjectManager\Code\Generator\Repository;
use Magento\Framework\ObjectManager\Code\Generator\Persistor;
use Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator;
use Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator;
use Magento\Setup\Module\Di\Code\Scanner;
use Magento\Setup\Module\Di\Compiler\Log\Log;
use Magento\Setup\Module\Di\Compiler\Log\Writer;
use Magento\Setup\Module\Di\Definition\Compressor;
use Magento\Setup\Module\Di\Definition\Serializer\Igbinary;
use Magento\Setup\Module\Di\Definition\Serializer\Standard;
use \Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Command to generate all non-existing proxies and factories, and pre-compile class definitions,
 * inheritance information and plugin definitions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiCompileMultiTenantCommand extends AbstractSetupCommand
{
    /**#@+
     * Names of input options
     */
    const INPUT_KEY_SERIALIZER = 'serializer';
    const INPUT_KEY_EXTRA_CLASSES_FILE = 'extra-classes-file';
    const INPUT_KEY_GENERATION = 'generation';
    const INPUT_KEY_DI= 'di';
    const INPUT_KEY_EXCLUDE_PATTERN= 'exclude-pattern';
    /**#@- */

    /**#@+
     * Possible values for serializer
     */
    const SERIALIZER_VALUE_SERIALIZE = 'serialize';
    const SERIALIZER_VALUE_IGBINARY = 'igbinary';
    /**#@- */

    /** Command name */
    const NAME = 'setup:di:compile-multi-tenant';

    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Filesystem Directory List
     *
     * @var DirectoryList
     */
    private $directoryList;

    /**
     *
     * @var array
     */
    private $entities;

    /**
     *
     * @var array
     */
    private $files = [];

    /**
     *
     * @var \Magento\Framework\Code\Generator
     */
    private $generator;

    /**
     *
     * @var Log
     */
    private $log;

    /**
     * @var ComponentRegistrar
     */
    private $componentRegistrar;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param DirectoryList $directoryList
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(
        ObjectManagerProvider $objectManagerProvider,
        DirectoryList $directoryList,
        ComponentRegistrar $componentRegistrar
    ) {
        $this->objectManager = $objectManagerProvider->get();
        $this->directoryList = $directoryList;
        $this->componentRegistrar = $componentRegistrar;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_SERIALIZER,
                null,
                InputOption::VALUE_REQUIRED,
                'Serializer function that should be used (' . self::SERIALIZER_VALUE_SERIALIZE . '|'
                . self::SERIALIZER_VALUE_IGBINARY . ') default: ' . self::SERIALIZER_VALUE_SERIALIZE
            ),
            new InputOption(
                self::INPUT_KEY_EXTRA_CLASSES_FILE,
                null,
                InputOption::VALUE_REQUIRED,
                'Path to file with extra proxies and factories to generate'
            ),
            new InputOption(
                self::INPUT_KEY_GENERATION,
                null,
                InputOption::VALUE_REQUIRED,
                'Absolute path to generated classes, <magento_root>/var/generation by default'
            ),
            new InputOption(
                self::INPUT_KEY_DI,
                null,
                InputOption::VALUE_REQUIRED,
                'Absolute path to DI definitions directory, <magento_root>/var/di by default'
            ),
            new InputOption(
                self::INPUT_KEY_EXCLUDE_PATTERN,
                null,
                InputOption::VALUE_REQUIRED,
                'Allows to exclude Paths from compilation (default is #[\\\\/]m1[\\\\/]#i)'
            ),
        ];
        $this->setName(self::NAME)
            ->setDescription(
                'Generates all non-existing proxies and factories, and pre-compile class definitions, '
                . 'inheritance information and plugin definitions'
            )
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * Get module directories exclude patterns
     *
     * @return array
     */
    private function getModuleExcludePatterns()
    {
        $modulesExcludePatterns = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $modulePath) {
            $modulesExcludePatterns[] = "#^" . $modulePath . "/Test#";
        }
        return $modulesExcludePatterns;
    }

    /**
     * Get library directories exclude patterns
     *
     * @return array
     */
    private function getLibraryExcludePatterns()
    {
        $libraryExcludePatterns = [];
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::LIBRARY) as $libraryPath) {
            $libraryExcludePatterns[] = "#^" . $libraryPath . "/([\\w]+/)?Test#";
        }
        return $libraryExcludePatterns;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errors = $this->validate($input);
        if ($errors) {
            $output->writeln($errors);
            return;
        }

        $generationDir = $input->getOption(self::INPUT_KEY_GENERATION) ? $input->getOption(self::INPUT_KEY_GENERATION)
            : $this->directoryList->getPath(DirectoryList::GENERATION);
        $modulesExcludePatterns = $this->getModuleExcludePatterns();
        $testExcludePatterns = [
            "#^" . $this->directoryList->getPath(DirectoryList::SETUP) . "/[\\w]+/[\\w]+/Test#",
            "#^" . $this->directoryList->getRoot() . "/dev/tools/Magento/Tools/[\\w]+/Test#"
        ];
        $librariesExcludePatterns = $this->getLibraryExcludePatterns();
        $testExcludePatterns = array_merge($testExcludePatterns, $modulesExcludePatterns, $librariesExcludePatterns);
        $fileExcludePatterns = $input->getOption('exclude-pattern') ?
            [$input->getOption(self::INPUT_KEY_EXCLUDE_PATTERN)] : ['#[\\\\/]M1[\\\\/]#i'];
        $fileExcludePatterns = array_merge($fileExcludePatterns, $testExcludePatterns);
        /** @var Writer\Console logWriter Writer model for success messages */
        $logWriter = new Writer\Console($output);
        $this->log = new Log($logWriter, $logWriter);
        AutoloaderRegistry::getAutoloader()->addPsr4('Magento\\', $generationDir . '/Magento/');
        // 1 Code generation
        $this->generateCode($generationDir, $fileExcludePatterns, $input);
        // 2. Compilation
        $this->compileCode($generationDir, $fileExcludePatterns, $input);
        //Reporter
        $this->log->report();
        if (!$this->log->hasError()) {
            $output->writeln(
                '<info>On *nix systems, verify the Magento application has permissions to modify files '
                . 'created by the compiler in the "var" directory. For instance, if you run the Magento application '
                . 'using Apache, the owner of the files in the "var" directory should be the Apache user (example '
                . 'command: "chown -R www-data:www-data <MAGENTO_ROOT>/var" where MAGENTO_ROOT is the Magento '
                . 'root directory).</info>'
            );
        }
    }

    /**
     * Generate Code
     *
     * @param string $generationDir
     * @param array $fileExcludePatterns
     * @param InputInterface $input
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function generateCode($generationDir, $fileExcludePatterns, $input)
    {
        // 1.1 Code scan
        $filePatterns = ['php' => '/.*\.php$/', 'di' => '/\/etc\/([a-zA-Z_]*\/di|di)\.xml$/'];
        $directoryScanner = new Scanner\DirectoryScanner();
        foreach ($this->componentRegistrar->getPaths(ComponentRegistrar::MODULE) as $codeScanDir) {
            $this->files = array_merge_recursive(
                $this->files,
                $directoryScanner->scan($codeScanDir, $filePatterns, $fileExcludePatterns)
            );
        }
        $this->files['additional'] = [$input->getOption(self::INPUT_KEY_EXTRA_CLASSES_FILE)];
        $repositoryScanner = new Scanner\RepositoryScanner();
        $repositories = $repositoryScanner->collectEntities($this->files['di']);
        $scanner = new Scanner\CompositeScanner();
        $scanner->addChild(new Scanner\PhpScanner($this->log), 'php');
        $scanner->addChild(new Scanner\XmlScanner($this->log), 'di');
        $scanner->addChild(new Scanner\ArrayScanner(), 'additional');
        $this->entities = $scanner->collectEntities($this->files);
        $interceptorScanner = new Scanner\XmlInterceptorScanner();
        $this->entities['interceptors'] = $interceptorScanner->collectEntities($this->files['di']);
        // 1.2 Generation of Factory and Additional Classes
        $generatorIo = new \Magento\Framework\Code\Generator\Io(
            new \Magento\Framework\Filesystem\Driver\File(),
            $generationDir
        );
        $this->generator = new \Magento\Framework\Code\Generator(
            $generatorIo,
            [
                Interceptor::ENTITY_TYPE => 'Magento\Framework\Interception\Code\Generator\Interceptor',
                Proxy::ENTITY_TYPE => 'Magento\Framework\ObjectManager\Code\Generator\Proxy',
                Factory::ENTITY_TYPE => 'Magento\Framework\ObjectManager\Code\Generator\Factory',
                Mapper::ENTITY_TYPE => 'Magento\Framework\Api\Code\Generator\Mapper',
                Persistor::ENTITY_TYPE => 'Magento\Framework\ObjectManager\Code\Generator\Persistor',
                Repository::ENTITY_TYPE => 'Magento\Framework\ObjectManager\Code\Generator\Repository',
                Converter::ENTITY_TYPE => 'Magento\Framework\ObjectManager\Code\Generator\Converter',
                SearchResults::ENTITY_TYPE => 'Magento\Framework\Api\Code\Generator\SearchResults',
                ExtensionAttributesInterfaceGenerator::ENTITY_TYPE =>
                    'Magento\Framework\Api\Code\Generator\ExtensionAttributesInterfaceGenerator',
                ExtensionAttributesGenerator::ENTITY_TYPE =>
                    'Magento\Framework\Api\Code\Generator\ExtensionAttributesGenerator'
            ]
        );
        /** Initialize object manager for code generation based on configs */
        $this->generator->setObjectManager($this->objectManager);
        $generatorAutoloader = new \Magento\Framework\Code\Generator\Autoloader($this->generator);
        spl_autoload_register([$generatorAutoloader, 'load']);

        foreach ($repositories as $entityName) {
            switch ($this->generator->generateClass($entityName)) {
                case \Magento\Framework\Code\Generator::GENERATION_SUCCESS:
                    $this->log->add(Log::GENERATION_SUCCESS, $entityName);
                    break;
                case \Magento\Framework\Code\Generator::GENERATION_ERROR:
                    $this->log->add(Log::GENERATION_ERROR, $entityName);
                    break;
                case \Magento\Framework\Code\Generator::GENERATION_SKIP:
                default:
                    //no log
                    break;
            }
        }
        foreach (['php', 'additional'] as $type) {
            sort($this->entities[$type]);
            foreach ($this->entities[$type] as $entityName) {
                switch ($this->generator->generateClass($entityName)) {
                    case \Magento\Framework\Code\Generator::GENERATION_SUCCESS:
                        $this->log->add(Log::GENERATION_SUCCESS, $entityName);
                        break;
                    case \Magento\Framework\Code\Generator::GENERATION_ERROR:
                        $this->log->add(Log::GENERATION_ERROR, $entityName);
                        break;
                    case \Magento\Framework\Code\Generator::GENERATION_SKIP:
                    default:
                        //no log
                        break;
                }
            }
        }
    }

    /**
     * Compile Code
     *
     * @param string $generationDir
     * @param array $fileExcludePatterns
     * @param InputInterface $input
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function compileCode($generationDir, $fileExcludePatterns, $input)
    {
        $diDir = $input->getOption(self::INPUT_KEY_DI) ? $input->getOption(self::INPUT_KEY_DI) :
            $this->directoryList->getPath(DirectoryList::DI);
        $relationsFile = $diDir . '/relations.ser';
        $pluginDefFile = $diDir . '/plugins.ser';
        $compilationDirs = [
            $this->directoryList->getPath(DirectoryList::SETUP) . '/Magento/Setup/Module',
            $this->directoryList->getRoot() . '/dev/tools/Magento/Tools',
        ];
        $compilationDirs = array_merge(
            $compilationDirs,
            $this->componentRegistrar->getPaths(ComponentRegistrar::MODULE),
            $this->componentRegistrar->getPaths(ComponentRegistrar::LIBRARY)
        );
        $serializer = $input->getOption(self::INPUT_KEY_SERIALIZER) == Igbinary::NAME ? new Igbinary() : new Standard();
        // 2.1 Code scan
        $validator = new \Magento\Framework\Code\Validator();
        $validator->add(new \Magento\Framework\Code\Validator\ConstructorIntegrity());
        $validator->add(new \Magento\Framework\Code\Validator\ContextAggregation());
        $classesScanner = new \Magento\Setup\Module\Di\Code\Reader\ClassesScanner();
        $classesScanner->addExcludePatterns($fileExcludePatterns);
        $directoryInstancesNamesList = new \Magento\Setup\Module\Di\Code\Reader\Decorator\Directory(
            $this->log,
            new \Magento\Framework\Code\Reader\ClassReader(),
            $classesScanner,
            $validator,
            $generationDir
        );
        foreach ($compilationDirs as $path) {
            if (is_readable($path)) {
                $directoryInstancesNamesList->getList($path);
            }
        }
        $inheritanceScanner = new Scanner\InheritanceInterceptorScanner();
        $this->entities['interceptors'] = $inheritanceScanner->collectEntities(
            get_declared_classes(),
            $this->entities['interceptors']
        );
        // 2.1.1 Generation of Proxy and Interceptor Classes
        foreach (['interceptors', 'di'] as $type) {
            foreach ($this->entities[$type] as $entityName) {
                switch ($this->generator->generateClass($entityName)) {
                    case \Magento\Framework\Code\Generator::GENERATION_SUCCESS:
                        $this->log->add(Log::GENERATION_SUCCESS, $entityName);
                        break;
                    case \Magento\Framework\Code\Generator::GENERATION_ERROR:
                        $this->log->add(Log::GENERATION_ERROR, $entityName);
                        break;
                    case \Magento\Framework\Code\Generator::GENERATION_SKIP:
                    default:
                        //no log
                        break;
                }
            }
        }
        //2.1.2 Compile relations for Proxy/Interceptor classes
        $directoryInstancesNamesList->getList($generationDir);
        $relations = $directoryInstancesNamesList->getRelations();
        // 2.2 Compression
        $relationsFileDir = dirname($relationsFile);
        if (!file_exists($relationsFileDir)) {
            mkdir($relationsFileDir, DriverInterface::WRITEABLE_DIRECTORY_MODE, true);
        }
        $relations = array_filter($relations);
        file_put_contents($relationsFile, $serializer->serialize($relations));
        // 3. Plugin Definition Compilation
        $pluginScanner = new Scanner\CompositeScanner();
        $pluginScanner->addChild(new Scanner\PluginScanner(), 'di');
        $pluginDefinitions = [];
        $pluginList = $pluginScanner->collectEntities($this->files);
        $pluginDefinitionList = new \Magento\Framework\Interception\Definition\Runtime();
        foreach ($pluginList as $type => $entityList) {
            foreach ($entityList as $entity) {
                $pluginDefinitions[ltrim($entity, '\\')] = $pluginDefinitionList->getMethodList($entity);
            }
        }
        $outputContent = $serializer->serialize($pluginDefinitions);
        $pluginDefFileDir = dirname($pluginDefFile);
        if (!file_exists($pluginDefFileDir)) {
            mkdir($pluginDefFileDir, DriverInterface::WRITEABLE_DIRECTORY_MODE, true);
        }
        file_put_contents($pluginDefFile, $outputContent);
    }

    /**
     * Check if all option values provided by the user are valid
     *
     * @param InputInterface $input
     * @return string[]
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function validate(InputInterface $input)
    {
        $errors = [];
        $options = $input->getOptions();
        foreach ($options as $key => $value) {
            if (!$value) {
                continue;
            }
            switch ($key) {
                case self::INPUT_KEY_SERIALIZER:
                    if (($value !== self::SERIALIZER_VALUE_SERIALIZE) && ($value !== self::SERIALIZER_VALUE_IGBINARY)) {
                        $errors[] = '<error>Invalid value for command option \'' . self::INPUT_KEY_SERIALIZER
                            . '\'. Possible values (' . self::SERIALIZER_VALUE_SERIALIZE . '|'
                            . self::SERIALIZER_VALUE_IGBINARY . ').</error>';
                    }
                    break;
                case self::INPUT_KEY_EXTRA_CLASSES_FILE:
                    if (!file_exists($value)) {
                        $errors[] = '<error>Path does not exist for the value of command option \''
                            . self::INPUT_KEY_EXTRA_CLASSES_FILE . '\'.</error>';
                    }
                    break;
                case self::INPUT_KEY_GENERATION:
                    $errorMsg = $this->validateOutputPath($value, self::INPUT_KEY_GENERATION);
                    if ($errorMsg !== '') {
                        $errors[] = $errorMsg;
                    }
                    break;
                case self::INPUT_KEY_DI:
                    $errorMsg = $this->validateOutputPath($value, self::INPUT_KEY_DI);
                    if ($errorMsg !== '') {
                        $errors[] = $errorMsg;
                    }
                    break;
                case self::INPUT_KEY_EXCLUDE_PATTERN:
                    if (@preg_match($value, null) === false) {
                        $errors[] = '<error>Invalid pattern for command option \'' . self::INPUT_KEY_EXCLUDE_PATTERN
                            . '\'.</error>';
                    }
                    break;
            }
        }
        return $errors;
    }

    /**
     * Validate output path based on type
     *
     * @param string $value
     * @param string $type
     * @return string
     */
    private function validateOutputPath($value, $type)
    {
        $errorMsg = '';
        if (!file_exists($value)) {
            $errorMsg = '<error>Path does not exist for the value of command option \'' . $type . '\'.</error>';
        }
        if (file_exists($value) && !is_writeable($value)) {
            $errorMsg .= '<error>Non-writable directory is provided by the value of command option \''
                . $type . '\'.</error>';

        }
        return $errorMsg;
    }
}
