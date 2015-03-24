<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/../../../bootstrap.php';

$rootDir = realpath(__DIR__ . '/../../../../../');
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
use Magento\Tools\Di\Code\Scanner;
use Magento\Tools\Di\Compiler\Log\Log;
use Magento\Tools\Di\Compiler\Log\Writer;
use Magento\Tools\Di\Definition\Compressor;
use Magento\Tools\Di\Definition\Serializer\Igbinary;
use Magento\Tools\Di\Definition\Serializer\Standard;

try {
    $opt = new Zend_Console_Getopt(
        [
            'serializer=w'         => 'serializer function that should be used (serialize|igbinary) default: serialize',
            'verbose|v'            => 'output report after tool run',
            'extra-classes-file=s' => 'path to file with extra proxies and factories to generate',
            'generation=s'         => 'absolute path to generated classes, <magento_root>/var/generation by default',
            'di=s'                 => 'absolute path to DI definitions directory, <magento_root>/var/di by default',
            'exclude-pattern=s'    => 'allows to exclude Paths from compilation (default is #[\\\\/]m1[\\\\/]#i)',
        ]
    );
    $opt->parse();

    $generationDir = $opt->getOption('generation') ? $opt->getOption('generation') : $rootDir . '/var/generation';
    $diDir = $opt->getOption('di') ? $opt->getOption('di') : $rootDir . '/var/di';

    $testExcludePatterns = [
        "#^$rootDir/app/code/[\\w]+/[\\w]+/Test#",
        "#^$rootDir/lib/internal/[\\w]+/[\\w]+/([\\w]+/)?Test#",
        "#^$rootDir/setup/src/Magento/Setup/Test#",
        "#^$rootDir/dev/tools/Magento/Tools/[\\w]+/Test#"
    ];
    $fileExcludePatterns = $opt->getOption('exclude-pattern') ?
        [$opt->getOption('exclude-pattern')] : ['#[\\\\/]M1[\\\\/]#i'];
    $fileExcludePatterns = array_merge($fileExcludePatterns, $testExcludePatterns);

    $relationsFile = $diDir . '/relations.ser';
    $pluginDefFile = $diDir . '/plugins.ser';

    $compilationDirs = [
        $rootDir . '/app/code',
        $rootDir . '/lib/internal/Magento',
        $rootDir . '/dev/tools/Magento/Tools'
    ];

    /** @var Writer\WriterInterface $logWriter Writer model for success messages */
    $logWriter = $opt->getOption('v') ? new Writer\Console() : new Writer\Quiet();
    $log = new Log($logWriter, new Writer\Console());

    $serializer = $opt->getOption('serializer') == Igbinary::NAME ? new Igbinary() : new Standard();

    AutoloaderRegistry::getAutoloader()->addPsr4('Magento\\', $generationDir . '/Magento/');

    // 1 Code generation
    // 1.1 Code scan
    $filePatterns = ['php' => '/.*\.php$/', 'di' => '/\/etc\/([a-zA-Z_]*\/di|di)\.xml$/'];
    $codeScanDir = realpath($rootDir . '/app');
    $directoryScanner = new Scanner\DirectoryScanner();
    $files = $directoryScanner->scan($codeScanDir, $filePatterns, $fileExcludePatterns);
    $files['additional'] = [$opt->getOption('extra-classes-file')];
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
    $magentoObjectManagerFactory = \Magento\Framework\App\Bootstrap::createObjectManagerFactory(BP, $_SERVER);
    $objectManager = $magentoObjectManagerFactory->create($_SERVER);
    $generator->setObjectManager($objectManager);

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
    $classesScanner = new \Magento\Tools\Di\Code\Reader\ClassesScanner();
    $classesScanner->addExcludePatterns($fileExcludePatterns);

    $directoryInstancesNamesList = new \Magento\Tools\Di\Code\Reader\Decorator\Directory(
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

    $output = $serializer->serialize($pluginDefinitions);

    if (!file_exists(dirname($pluginDefFile))) {
        mkdir(dirname($pluginDefFile), 0777, true);
    }

    file_put_contents($pluginDefFile, $output);

    //Reporter
    $log->report();

    if ($log->hasError()) {
        exit(1);
    }

    echo 'On *nix systems, verify the Magento application has permissions to modify files created by the compiler'
        . ' in the "var" directory. For instance, if you run the Magento application using Apache,'
        . ' the owner of the files in the "var" directory should be the Apache user (example command:'
        . ' "chown -R www-data:www-data <MAGENTO_ROOT>/var" where MAGENTO_ROOT is the Magento root directory).' . "\n";
    /** TODO: Temporary solution before having necessary changes on bamboo to overcome issue described above */
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootDir . '/var'));
    foreach ($iterator as $item) {
        chmod($item, 0777);
    }

} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    echo 'Please, use quotes(") for wrapping strings.' . "\n";
    exit(1);
} catch (Exception $e) {
    fwrite(STDERR, "Compiler failed with exception: " . $e->getMessage());
    throw($e);
}
