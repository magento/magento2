<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/../../../bootstrap.php';

$rootDir = realpath(__DIR__ . '/../../../../../');
use Magento\Framework\Api\Code\Generator\DataBuilder;
use Magento\Framework\Api\Code\Generator\Mapper;
use Magento\Framework\Api\Code\Generator\SearchResults;
use Magento\Framework\Api\Code\Generator\SearchResultsBuilder;
use Magento\Framework\Autoload\AutoloaderRegistry;
use Magento\Framework\Interception\Code\Generator\Interceptor;
use Magento\Framework\ObjectManager\Code\Generator\Converter;
use Magento\Framework\ObjectManager\Code\Generator\Factory;
use Magento\Framework\ObjectManager\Code\Generator\Proxy;
use Magento\Framework\ObjectManager\Code\Generator\Repository;
use Magento\Tools\Di\Code\Scanner;
use Magento\Tools\Di\Compiler\Directory;
use Magento\Tools\Di\Compiler\Log\Log;
use Magento\Tools\Di\Compiler\Log\Writer;
use Magento\Tools\Di\Definition\Compressor;
use Magento\Tools\Di\Definition\Serializer;

$filePatterns = ['php' => '/.*\.php$/', 'di' => '/\/etc\/([a-zA-Z_]*\/di|di)\.xml$/'];
$codeScanDir = realpath($rootDir . '/app');
try {
    $opt = new Zend_Console_Getopt(
        [
            'serializer=w'         => 'serializer function that should be used (serialize|igbinary) default: serialize',
            'verbose|v'            => 'output report after tool run',
            'extra-classes-file=s' => 'path to file with extra proxies and factories to generate',
            'generation=s'         => 'absolute path to generated classes, <magento_root>/var/generation by default',
            'di=s'                 => 'absolute path to DI definitions directory, <magento_root>/var/di by default',
        ]
    );
    $opt->parse();

    $generationDir = $opt->getOption('generation') ? $opt->getOption('generation') : $rootDir . '/var/generation';
    $diDir = $opt->getOption('di') ? $opt->getOption('di') : $rootDir . '/var/di';
    $compiledFile = $diDir . '/definitions.php';
    $relationsFile = $diDir . '/relations.ser';
    $pluginDefFile = $diDir . '/plugins.ser';

    $compilationDirs = [
        $rootDir . '/app/code',
        $rootDir . '/lib/internal/Magento',
        $rootDir . '/dev/tools/Magento/Tools/View'
    ];

    /** @var Writer\WriterInterface $logWriter Writer model for success messages */
    $logWriter = $opt->getOption('v') ? new Writer\Console() : new Writer\Quiet();

    /** @var Writer\WriterInterface $logWriter Writer model for error messages */
    $errorWriter = new Writer\Console();

    $log = new Log($logWriter, $errorWriter);
    $serializer = $opt->getOption('serializer') == 'igbinary' ? new Serializer\Igbinary() : new Serializer\Standard();

    $validator = new \Magento\Framework\Code\Validator();
    $validator->add(new \Magento\Framework\Code\Validator\ConstructorIntegrity());
    $validator->add(new \Magento\Framework\Code\Validator\ContextAggregation());

    AutoloaderRegistry::getAutoloader()->addPsr4('Magento\\', $generationDir . '/Magento/');

    // 1 Code generation
    // 1.1 Code scan
    $directoryScanner = new Scanner\DirectoryScanner();
    $files = $directoryScanner->scan($codeScanDir, $filePatterns);
    $files['additional'] = [$opt->getOption('extra-classes-file')];
    $entities = [];

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
            DataBuilder::ENTITY_TYPE => 'Magento\Framework\Api\Code\Generator\DataBuilder',
            Interceptor::ENTITY_TYPE => 'Magento\Framework\Interception\Code\Generator\Interceptor',
            SearchResultsBuilder::ENTITY_TYPE => 'Magento\Framework\Api\Code\Generator\SearchResultsBuilder',
            DataBuilder::ENTITY_TYPE_BUILDER  => 'Magento\Framework\Api\Code\Generator\DataBuilder',
            Proxy::ENTITY_TYPE => 'Magento\Framework\ObjectManager\Code\Generator\Proxy',
            Factory::ENTITY_TYPE => 'Magento\Framework\ObjectManager\Code\Generator\Factory',
            Mapper::ENTITY_TYPE => 'Magento\Framework\Api\Code\Generator\Mapper',
            Repository::ENTITY_TYPE => 'Magento\Framework\ObjectManager\Code\Generator\Repository',
            Converter::ENTITY_TYPE => 'Magento\Framework\ObjectManager\Code\Generator\Converter',
            SearchResults::ENTITY_TYPE => 'Magento\Framework\Api\Code\Generator\SearchResults',
        ]
    );

    $generatorAutoloader = new \Magento\Framework\Code\Generator\Autoloader($generator);
    spl_autoload_register([$generatorAutoloader, 'load']);
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
    $directoryCompiler = new Directory($log, $validator);
    foreach ($compilationDirs as $path) {
        if (is_readable($path)) {
            $directoryCompiler->compile($path);
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

    //2.1.2 Compile definitions for Proxy/Interceptor classes
    $directoryCompiler->compile($generationDir, false);

    list($definitions, $relations) = $directoryCompiler->getResult();

    // 2.2 Compression
    $compressor = new Compressor($serializer);
    $output = $compressor->compress($definitions);
    if (!file_exists(dirname($compiledFile))) {
        mkdir(dirname($compiledFile), 0777, true);
    }
    $relations = array_filter($relations);

    file_put_contents($compiledFile, $output);
    file_put_contents($relationsFile, $serializer->serialize($relations));

    // 3. Plugin Definition Compilation
    $pluginScanner = new Scanner\CompositeScanner();
    $pluginScanner->addChild(new Scanner\PluginScanner(), 'di');
    $pluginDefinitions = [];
    $pluginList = $pluginScanner->collectEntities($files);
    $pluginDefinitionList = new \Magento\Framework\Interception\Definition\Runtime();
    foreach ($pluginList as $type => $entityList) {
        foreach ($entityList as $entity) {
            $pluginDefinitions[$entity] = $pluginDefinitionList->getMethodList($entity);
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
} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    echo 'Please, use quotes(") for wrapping strings.' . "\n";
    exit(1);
} catch (Exception $e) {
    fwrite(STDERR, "Compiler failed with exception: " . $e->getMessage());
    throw($e);
}
