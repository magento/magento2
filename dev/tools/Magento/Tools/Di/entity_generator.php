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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

use Magento\Framework\Code\Generator;
use Magento\Framework\Code\Generator\Io;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\ObjectManager\Code\Generator\Factory;
use Magento\Framework\ObjectManager\Code\Generator\Proxy;
use Magento\Framework\Interception\Code\Generator\Interceptor;
use Magento\Framework\Exception;
use Magento\Framework\Service\Code\Generator\Builder;
use Magento\Framework\Service\Code\Generator\Mapper;
use Magento\Framework\ObjectManager\Code\Generator\Repository;
use Magento\Framework\ObjectManager\Code\Generator\Converter;
use Magento\Framework\Service\Code\Generator\SearchResults;
use Magento\Framework\Service\Code\Generator\SearchResultsBuilder;

require __DIR__ . '/../../../../../app/bootstrap.php';

// default generation dir
$generationDir = BP . '/' . Io::DEFAULT_DIRECTORY;

try {
    $opt = new \Zend_Console_Getopt(
        [
            'type|t=w' => 'entity type(required)',
            'class|c=s' => 'entity class name(required)',
            'generation|g=s' => 'generation dir. Default value ' . $generationDir
        ]
    );
    $opt->parse();

    $entityType = $opt->getOption('t');
    if (empty($entityType)) {
        throw new \Zend_Console_Getopt_Exception('type is a required parameter');
    }

    $className = $opt->getOption('c');
    if (empty($className)) {
        throw new \Zend_Console_Getopt_Exception('class is a required parameter');
    }
    $substitutions = ['proxy' => '_Proxy', 'factory' => 'Factory', 'interceptor' => '_Interceptor'];
    if (!in_array($entityType, array_keys($substitutions))) {
        throw new \Zend_Console_Getopt_Exception('unrecognized type: ' . $entityType);
    }
    $className .= $substitutions[$entityType];

    if ($opt->getOption('g')) {
        $generationDir = $opt->getOption('g');
    }
} catch (\Zend_Console_Getopt_Exception $e) {
    $generator = new Generator();
    $entities = $generator->getGeneratedEntities();

    $allowedTypes = 'Allowed entity types are: ' . implode(', ', $entities) . '.';
    $example = 'Example: php -f entity_generator.php -- -t factory -c \Magento\Framework\Event\Observer ' .
        '-g /var/mage/m2ee/generation' .
        ' - will generate file /var/mage/m2ee/generation/Magento/Framework/Event/ObserverFactory.php';

    echo $e->getMessage() . "\n";
    echo $e->getUsageMessage() . "\n";
    echo $allowedTypes . "\n";
    echo 'Default generation dir is ' . $generationDir . "\n";
    exit($example);
}

(new \Magento\Framework\Autoload\IncludePath())->addIncludePath($generationDir);

//reinit generator with correct generation path
$io = new Io(new File(), null, $generationDir);
$generator = new Generator(
    null,
    $io,
    [
        SearchResultsBuilder::ENTITY_TYPE =>
            'Magento\Framework\Service\Code\Generator\SearchResultsBuilder',
        Proxy::ENTITY_TYPE =>
            'Magento\Framework\ObjectManager\Code\Generator\Proxy',
        Factory::ENTITY_TYPE =>
            'Magento\Framework\ObjectManager\Code\Generator\Factory',
        Interceptor::ENTITY_TYPE =>
            'Magento\Framework\Interception\Code\Generator\Interceptor',
        Builder::ENTITY_TYPE =>
            'Magento\Framework\Service\Code\Generator\Builder',
        Mapper::ENTITY_TYPE =>
            'Magento\Framework\Service\Code\Generator\Mapper',
        Repository::ENTITY_TYPE =>
            'Magento\Framework\ObjectManager\Code\Generator\Repository',
        Converter::ENTITY_TYPE =>
            'Magento\Framework\ObjectManager\Code\Generator\Converter',
        SearchResults::ENTITY_TYPE =>
            'Magento\Framework\Service\Code\Generator\SearchResults',
    ]
);

try {
    if (Generator::GENERATION_SUCCESS == $generator->generateClass($className)) {
        print "Class {$className} was successfully generated.\n";
    } else {
        print "Can't generate class {$className}. This class either not generated entity, or it already exists.\n";
    }
} catch (Exception $e) {
    print "Error! {$e->getMessage()}\n";
}
