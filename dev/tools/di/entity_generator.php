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

require __DIR__ . '/../../../app/bootstrap.php';

$generator = new Magento_Di_Generator();
$generatedEntities = $generator->getGeneratedEntities();
if (!isset($argv[1]) || in_array($argv[1], array('-?', '/?', '-help', '--help'))) {
    $message = " * Usage: php entity_generator.php [" . implode('|', $generatedEntities)
        . "] <required_entity_class_name>\n"
        . " * Example: php entity_generator.php factory Mage_Tag_Model_Tag"
        . " - will generate file var/generation/Mage/Tag/Model/TagFactory.php\n";
    print($message);
    exit();
}

$entityType = $argv[1];
if (!in_array($argv[1], $generatedEntities)) {
    print "Error! Unknown entity type.\n";
    exit();
}

if (!isset($argv[2])) {
    print "Error! Please, specify class name.\n";
    exit();
}
$className = $argv[2] . ucfirst($entityType);

try {
    if ($generator->generateClass($className)) {
        print("Class {$className} was successfully generated.\n");
    } else {
        print("Can't generate class {$className}. This class either not generated entity, or it already exists.\n");
    }
} catch (Magento_Exception $e) {
    print("Error! {$e->getMessage()}\n");
}
