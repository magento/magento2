<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/bootstrap.php';
use Magento\Tools\I18n\ServiceLocator;

try {
    $console = new \Zend_Console_Getopt(
        [
            'source|s=s' => 'Path to source dictionary file with translations',
            'pack|p=s' => 'Path to language package',
            'locale|l=s' => 'Target locale for dictionary, for example "de_DE"',
            'mode|m-s' => 'Save mode for dictionary
        - "replace" - replace language pack by new one
        - "merge" -  merge language packages, by default "replace"',
            'allow_duplicates|d=s' => 'Is allowed to save duplicates of translate, by default "no"',
        ]
    );
    $console->parse();
    if (!count($console->getOptions())) {
        throw new \UnexpectedValueException(
            'Required parameters are missed, please see usage description' . "\n\n" . $console->getUsageMessage()
        );
    }
    $dictionaryPath = $console->getOption('source');
    $packPath = $console->getOption('pack');
    $locale = $console->getOption('locale');
    $allowDuplicates = in_array($console->getOption('allow_duplicates'), ['y', 'Y', 'yes', 'Yes', '1']);
    $saveMode = $console->getOption('mode');

    if (!$dictionaryPath) {
        throw new \Zend_Console_Getopt_Exception('Dictionary source path parameter is required.');
    }
    if (!$packPath) {
        throw new \Zend_Console_Getopt_Exception('Pack path parameter is required.');
    }
    if (!$locale) {
        throw new \Zend_Console_Getopt_Exception('Locale parameter is required.');
    }

    $generator = ServiceLocator::getPackGenerator();
    $generator->generate($dictionaryPath, $packPath, $locale, $saveMode, $allowDuplicates);

    fwrite(STDOUT, sprintf("\nSuccessfully saved %s language package.\n", $locale));
} catch (\Zend_Console_Getopt_Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n\n" . $e->getUsageMessage() . "\n");
    exit(1);
} catch (\Exception $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
