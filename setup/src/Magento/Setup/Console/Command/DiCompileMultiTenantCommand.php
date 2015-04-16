<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Store\Model\StoreManager;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Console\ObjectManagerProvider;
use Magento\Framework\App\ObjectManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\Api\Code\Generator\Mapper;
use Magento\Framework\Api\Code\Generator\SearchResults;
use Magento\Framework\Autoload\AutoloaderRegistry;
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

/**
 * Command to generate all non-existing proxies and factories, and pre-compile class definitions,
 * inheritance information and plugin definitions
 */
class DiCompileMultiTenantCommand extends Command
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

    /**
     * Object Manager
     *
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * Constructor
     *
     * @param ObjectManagerProvider $objectManagerProvider
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(ObjectManagerProvider $objectManagerProvider, DeploymentConfig $deploymentConfig)
    {
        $this->objectManager = $objectManagerProvider->get();
        $this->deploymentConfig = $deploymentConfig;
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
                'Serializer function that should be used (serialize|igbinary) default: serialize'
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
        $this->setName('setup:di:compile-multi-tenant')
            ->setDescription(
                'Generates all non-existing proxies and factories, and pre-compile class definitions, '
                . 'inheritance information and plugin definitions'
            )
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->deploymentConfig->isAvailable()) {
            $output->writeln("<info>You cannot run this command as Magento application is not installed.</info>");
            return;
        }

        $rootDir = realpath(__DIR__ . '/../../../../../../');

        $generationDir = $input->getOption(self::INPUT_KEY_GENERATION) ? $input->getOption(self::INPUT_KEY_GENERATION)
            : $rootDir . '/var/generation';
        $diDir = $input->getOption(self::INPUT_KEY_DI) ? $input->getOption(self::INPUT_KEY_DI) : $rootDir . '/var/di';

        $testExcludePatterns = [
            "#^$rootDir/app/code/[\\w]+/[\\w]+/Test#",
            "#^$rootDir/lib/internal/[\\w]+/[\\w]+/([\\w]+/)?Test#",
            "#^$rootDir/setup/src/Magento/Setup/Test#",
            "#^$rootDir/dev/tools/Magento/Tools/[\\w]+/Test#"
        ];
        $fileExcludePatterns = $input->getOption('exclude-pattern') ?
            [$input->getOption(self::INPUT_KEY_EXCLUDE_PATTERN)] : ['#[\\\\/]M1[\\\\/]#i'];
        $fileExcludePatterns = array_merge($fileExcludePatterns, $testExcludePatterns);

        $relationsFile = $diDir . '/relations.ser';
        $pluginDefFile = $diDir . '/plugins.ser';

        $compilationDirs = [
            $rootDir . '/app/code',
            $rootDir . '/lib/internal/Magento',
            $rootDir . '/dev/tools/Magento/Tools'
        ];

        /** @var Writer\Console $logWriter Writer model for success messages */
        $logWriter = new Writer\Console($output);
        $log = new Log($logWriter, $logWriter);

        $serializer = $input->getOption(self::INPUT_KEY_SERIALIZER) == Igbinary::NAME ? new Igbinary() : new Standard();

        AutoloaderRegistry::getAutoloader()->addPsr4('Magento\\', $generationDir . '/Magento/');

        // 1 Code generation
        // 1.1 Code scan
        $filePatterns = ['php' => '/.*\.php$/', 'di' => '/\/etc\/([a-zA-Z_]*\/di|di)\.xml$/'];
        $codeScanDir = realpath($rootDir . '/app');
        $directoryScanner = new Scanner\DirectoryScanner();
        $files = $directoryScanner->scan($codeScanDir, $filePatterns, $fileExcludePatterns);
        $files['additional'] = [$input->getOption(self::INPUT_KEY_EXTRA_CLASSES_FILE)];
        $entities = [];
        $repositoryScanner = new Scanner\RepositoryScanner();
        $repositories = $repositoryScanner->collectEntities($files['di']);
        $scanner = new Scanner\CompositeScanner();
        $scanner->addChild(new Scanner\PhpScanner($log), 'php');
        $scanner->addChild(new Scanner\XmlScanner($log), 'di');
        $scanner->addChild(new Scanner\ArrayScanner(), 'additional');
        $entities = $scanner->collectEntities($files);
        $interceptorScanner = new Scanner\XmlInterceptorScanner();
        $entities['interceptors'] = $interceptorScanner->collectEntities($files['di']);

        // 1.2 Generation of Factory and Additional Classes
        $generatorIo = new \Magento\Framework\Code\Generator\Io(
            new \Magento\Framework\Filesystem\Driver\File(),
            $generationDir
        );
        $generator = new \Magento\Framework\Code\Generator(
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
        $generator->setObjectManager($this->objectManager);
        $generatorAutoloader = new \Magento\Framework\Code\Generator\Autoloader($generator);
        spl_autoload_register([$generatorAutoloader, 'load']);
        foreach ($repositories as $entityName) {
            switch ($generator->generateClass($entityName)) {
                case \Magento\Framework\Code\Generator::GENERATION_SUCCESS:
                    $log->add(Log::GENERATION_SUCCESS, $entityName);
                    break;

                case \Magento\Framework\Code\Generator::GENERATION_ERROR:
                    $log->add(Log::GENERATION_ERROR, $entityName);
                    break;

                case \Magento\Framework\Code\Generator::GENERATION_SKIP:
                default:
                    //no log
                    break;
            }
        }
        foreach (['php', 'additional'] as $type) {
            sort($entities[$type]);
            foreach ($entities[$type] as $entityName) {
                switch ($generator->generateClass($entityName)) {
                    case \Magento\Framework\Code\Generator::GENERATION_SUCCESS:
                        $log->add(Log::GENERATION_SUCCESS, $entityName);
                        break;

                    case \Magento\Framework\Code\Generator::GENERATION_ERROR:
                        $log->add(Log::GENERATION_ERROR, $entityName);
                        break;

                    case \Magento\Framework\Code\Generator::GENERATION_SKIP:
                    default:
                        //no log
                        break;
                }
            }
        }

        // 2. Compilation
        // 2.1 Code scan
        $validator = new \Magento\Framework\Code\Validator();
        $validator->add(new \Magento\Framework\Code\Validator\ConstructorIntegrity());
        $validator->add(new \Magento\Framework\Code\Validator\ContextAggregation());
        $classesScanner = new \Magento\Setup\Module\Di\Code\Reader\ClassesScanner();
        $classesScanner->addExcludePatterns($fileExcludePatterns);

        $directoryInstancesNamesList = new \Magento\Setup\Module\Di\Code\Reader\Decorator\Directory(
            $log,
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
        $entities['interceptors'] = $inheritanceScanner->collectEntities(
            get_declared_classes(),
            $entities['interceptors']
        );

        // 2.1.1 Generation of Proxy and Interceptor Classes
        foreach (['interceptors', 'di'] as $type) {
            foreach ($entities[$type] as $entityName) {
                switch ($generator->generateClass($entityName)) {
                    case \Magento\Framework\Code\Generator::GENERATION_SUCCESS:
                        $log->add(Log::GENERATION_SUCCESS, $entityName);
                        break;

                    case \Magento\Framework\Code\Generator::GENERATION_ERROR:
                        $log->add(Log::GENERATION_ERROR, $entityName);
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
        if (!file_exists(dirname($relationsFile))) {
            mkdir(dirname($relationsFile), 0777, true);
        }
        $relations = array_filter($relations);
        file_put_contents($relationsFile, $serializer->serialize($relations));

        // 3. Plugin Definition Compilation
        $pluginScanner = new Scanner\CompositeScanner();
        $pluginScanner->addChild(new Scanner\PluginScanner(), 'di');
        $pluginDefinitions = [];
        $pluginList = $pluginScanner->collectEntities($files);
        $pluginDefinitionList = new \Magento\Framework\Interception\Definition\Runtime();
        foreach ($pluginList as $type => $entityList) {
            foreach ($entityList as $entity) {
                $pluginDefinitions[ltrim($entity, '\\')] = $pluginDefinitionList->getMethodList($entity);
            }
        }

        $outputContent = $serializer->serialize($pluginDefinitions);

        if (!file_exists(dirname($pluginDefFile))) {
            mkdir(dirname($pluginDefFile), 0777, true);
        }

        file_put_contents($pluginDefFile, $outputContent);

        //Reporter
        $log->report();

        if ($log->hasError()) {
            exit(1);
        }

        $output->writeln('<info>On *nix systems, verify the Magento application has permissions to modify files '
            . 'created by the compiler in the "var" directory. For instance, if you run the Magento application using '
            . 'Apache, the owner of the files in the "var" directory should be the Apache user (example command:'
            . ' "chown -R www-data:www-data <MAGENTO_ROOT>/var" where MAGENTO_ROOT is the Magento root directory).'
            . '</info>');
    }
}
