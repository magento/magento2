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
require_once __DIR__ . '/bootstrap.php';
use Magento\Tools\I18n\Code\ServiceLocator;

try {
    $console = new \Zend_Console_Getopt(
        array(
            'directory|d=s' => 'Path to a directory to parse',
            'output-file|o=s' => 'Path (with filename) to output file, '
                . 'by default output the results into standard output stream',
            'magento|m-s' => 'Indicates whether the specified "directory" path is a Magento root directory,'
                . ' "no" by default'
        )
    );
    $console->parse();

    if (!count($console->getOptions())) {
        throw new \Zend_Console_Getopt_Exception(
            'Required parameters are missed, please see usage description',
            $console->getUsageMessage()
        );
    }
    $directory = $console->getOption('directory');
    if (empty($directory)) {
        throw new \Zend_Console_Getopt_Exception('Directory is a required parameter.', $console->getUsageMessage());
    }
    $outputFilename = $console->getOption('output-file') ?: null;
    $isMagento = in_array($console->getOption('magento'), array('y', 'yes', 'Y', 'Yes', 'YES', '1'));

    $generator = ServiceLocator::getDictionaryGenerator();
    $generator->generate($directory, $outputFilename, $isMagento);

    fwrite(STDOUT, "\nDictionary successfully processed.\n");
} catch (\Zend_Console_Getopt_Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n\n" . $e->getUsageMessage() . "\n");
    exit(1);
} catch (\Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
