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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require __DIR__ . '/../../../../../app/bootstrap.php';

// default generation dir
$generationDir = BP . '/' . \Magento\Code\Generator\Io::DEFAULT_DIRECTORY;

try {
    $opt = new Zend_Console_Getopt(array(
        'type|t=w' => 'entity type(required)',
        'class|c=w' => 'entity class name(required)',
        'generation|g=s' => 'generation dir. Default value ' . $generationDir,
    ));
    $opt->parse();

    $entityType = $opt->getOption('t');
    if (empty($entityType)) {
        throw new Zend_Console_Getopt_Exception('type is a required parameter');
    }

    $className = $opt->getOption('c');
    if (empty($className)) {
        throw new Zend_Console_Getopt_Exception('class is a required parameter');
    }
    $substitutions = array('proxy' => '_Proxy', 'factory' => 'Factory', 'interceptor' => '_Interceptor');
    if (!in_array($entityType, array_keys($substitutions))) {
        throw new Zend_Console_Getopt_Exception('unrecognized type: ' . $entityType);
    }
    $className .= $substitutions[$entityType];

    if ($opt->getOption('g')) {
        $generationDir = $opt->getOption('g');
    }
} catch (Zend_Console_Getopt_Exception $e) {
    $generator = new \Magento\Code\Generator();
    $entities = $generator->getGeneratedEntities();

    $allowedTypes = 'Allowed entity types are: ' . implode(', ', $entities) . '.';
    $example = 'Example: php -f entity_generator.php -- -t factory -c \Magento\Event\Observer '
        . '-g /var/mage/m2ee/generation'
        . ' - will generate file /var/mage/m2ee/generation/Magento/Event/ObserverFactory.php';

    echo $e->getMessage() . "\n";
    echo $e->getUsageMessage() . "\n";
    echo $allowedTypes . "\n";
    echo 'Default generation dir is ' . $generationDir . "\n";
    die($example);
}

\Magento\Autoload\IncludePath::addIncludePath($generationDir);

//reinit generator with correct generation path
$io = new \Magento\Code\Generator\Io(new \Magento\Filesystem\Driver\File(), null, $generationDir);
$generator = new \Magento\Code\Generator(null, null, $io);

try {
    if (\Magento\Code\Generator::GENERATION_SUCCESS == $generator->generateClass($className)) {
        print("Class {$className} was successfully generated.\n");
    } else {
        print("Can't generate class {$className}. This class either not generated entity, or it already exists.\n");
    }
} catch (\Magento\Exception $e) {
    print("Error! {$e->getMessage()}\n");
}
