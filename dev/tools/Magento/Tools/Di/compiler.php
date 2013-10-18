<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Tools
 * @package    DI
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
require __DIR__ . '/../../../bootstrap.php';
$rootDir = realpath(__DIR__ . '/../../../../../');
use Magento\Tools\Di\Compiler\Log\Log,
    Magento\Tools\Di\Compiler\Log\Writer,
    Magento\Tools\Di\Compiler\Directory,
    Magento\Tools\Di\Code\Scanner,
    Magento\Tools\Di\Definition\Compressor,
    Magento\Tools\Di\Definition\Serializer;

$filePatterns = array(
    'php' => '/.*\.php$/',
    'etc' => '/\/app\/etc\/[a-z0-9\.]*\.xml$/',
    'di' => '/\/etc\/([a-zA-Z_]*\/di|di)\.xml$/',
    'view' => '/\/view\/[a-z0-9A-Z\/\.]*\.xml$/',
    'design' => '/\/app\/design\/[a-z0-9A-Z\/\._]*\.xml$/',
);
$codeScanDir = realpath($rootDir . '/app');
try {
    $opt = new Zend_Console_Getopt(array(
        'serializer=w' => 'serializer function that should be used (serialize|binary) default = serialize',
        'verbose|v' => 'output report after tool run',
        'log|l=s' => 'log level (all|error) default = all',
        'extra-classes-file=s' => 'path to file with extra proxies and factories to generate',
        'generation=s' => 'absolute path to generated classes, <magento_root>/var/generation by default',
        'di=s' => 'absolute path to DI definitions directory, <magento_root>/var/di by default'
    ));
    $opt->parse();

    $generationDir = $opt->getOption('generation') ? $opt->getOption('generation') : $rootDir . DS . 'var/generation';
    \Magento\Autoload\IncludePath::addIncludePath($generationDir);

    $diDir = $opt->getOption('di') ? $opt->getOption('di') : $rootDir . DS . 'var/di';
    $compiledFile = $diDir . DS . 'definitions.php';
    $relationsFile = $diDir . DS . 'relations.php';
    $pluginDefFile = $diDir . DS . 'plugins.php';

    $compilationDirs = array(
        $rootDir . DS . 'app/code',
        $rootDir . '/lib/Magento',
        $generationDir,
    );

    $writer = $opt->getOption('v') ? new Writer\Console() : new Writer\Quiet();
    $allowedLogTypes = $opt->getOption('log') == 'error' ?
        array(Log::COMPILATION_ERROR, Log::GENERATION_ERROR)
        : array();
    $log = new Log($writer, $allowedLogTypes);
    $serializer = ($opt->getOption('serializer') == 'binary') ? new Serializer\Igbinary() : new Serializer\Standard();

    // 1 Code generation
    // 1.1 Code scan
    $directoryScanner = new Scanner\DirectoryScanner();
    $files = $directoryScanner->scan($codeScanDir, $filePatterns);
    $files['additional'] = array($opt->getOption('extra-classes-file'));
    $entities = array();

    $scanner = new Scanner\CompositeScanner();
    $scanner->addChild(new Scanner\PhpScanner(), 'php');
    $scanner->addChild(new Scanner\XmlScanner(), 'etc');
    $scanner->addChild(new Scanner\XmlScanner(), 'di');
    $scanner->addChild(new Scanner\XmlScanner(), 'view');
    $scanner->addChild(new Scanner\XmlScanner(), 'design');
    $scanner->addChild(new Scanner\ArrayScanner(), 'additional');
    $entities = $scanner->collectEntities($files);

    $interceptorScanner = new Scanner\XmlInterceptorScanner();
    $entities = array_merge($entities, $interceptorScanner->collectEntities($files['di']));

    // 1.2 Generation
    $generatorIo = new \Magento\Code\Generator\Io(null, null, $generationDir);
    $generator = new \Magento\Code\Generator(null, null, $generatorIo);
    foreach ($entities as $entityName) {
        switch ($generator->generateClass($entityName)) {
            case \Magento\Code\Generator::GENERATION_SUCCESS:
                $log->add(Log::GENERATION_SUCCESS, $entityName);
                break;

            case \Magento\Code\Generator::GENERATION_ERROR:
                $log->add(Log::GENERATION_ERROR, $entityName);
                break;

            case \Magento\Code\Generator::GENERATION_SKIP:
            default:
                //no log
                break;
        }
    }

    // 2. Compilation
    // 2.1 Code scan
    $directoryCompiler = new Directory($log);
    foreach ($compilationDirs as $path) {
        if (is_readable($path)) {
            $directoryCompiler->compile($path);
        }
    }
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
    $pluginDefinitions = array();
    foreach ($pluginScanner->collectEntities($files) as $entity) {
        $pluginDefinitions[$entity] = get_class_methods($entity);
    }
    $output = $serializer->serialize($pluginDefinitions);

    if (!file_exists(dirname($pluginDefFile))) {
        mkdir(dirname($pluginDefFile), 0777, true);
    }

    file_put_contents($pluginDefFile, $output);
    //Reporter
    $log->report();
} catch (Zend_Console_Getopt_Exception $e) {
    echo $e->getUsageMessage();
    echo 'Please, use quotes(") for wrapping strings.' . "\n";
    exit(1);
} catch (Exception $e) {
    fwrite(STDERR, "Compiler failed with exception: " . $e->getMessage());
    exit(1);
}
