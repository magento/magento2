<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require_once __DIR__ . '/bootstrap.php';
use Magento\Tools\I18n\ServiceLocator;

try {
    $console = new \Zend_Console_Getopt(
        [
            'directory|d=s' => 'Path to a directory to parse',
            'output-file|o=s' => 'Path (with filename) to output file, '
                . 'by default output the results into standard output stream',
            'magento|m-s' => 'Indicates whether the specified "directory" path is a Magento root directory,'
                . ' "no" by default',
        ]
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
    $isMagento = in_array($console->getOption('magento'), ['y', 'yes', 'Y', 'Yes', 'YES', '1']);

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
