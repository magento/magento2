<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * PHP Code Sniffer CLI Wrapper
 */
namespace Magento\TestFramework\CodingStandard\Tool\CodeSniffer;

/**
 *
 * Class LessWrapper
 *
 * Class is used for adding elements into "$allowedFileExtensions" for PHP_CodeSniffer instance.
 * There is a method "PHP_CodeSniffer_CLI::setAllowedFileExtensions" that allows to redefine values of existing elements
 * (Tokenizers), but still only files with the following extensions are allowed "php, inc, js, css".
 *
 * For LESS Files it's need to have the following value for allowedFileExtensions: ['less' => 'CSS'].
 * This action was done in the following way:
 * ....
 * $phpcs->allowedFileExtensions += [self::LESS_FILE_EXTENSION => self::LESS_TOKENIZER];
 * ....
 *
 * At moment of implementation Code Sniffer has a version "1.5.3".
 * Versions > 2.0 have more advanced version of PHP_CodeSniffer_CLI::setAllowedFileExtensions method that allows to pass
 * extension + tokenizers without additional codding, for example:
 * ...
 * $codeSniffer->setExtensions('less/CSS');
 * ...
 *
 */
class LessWrapper extends Wrapper
{
    const LESS_FILE_EXTENSION = 'less';

    const LESS_TOKENIZER = 'CSS';

    /**
     * Runs PHP_CodeSniffer over files and directories
     *
     * @param array $values
     * @return int
     * @throws \PHP_CodeSniffer_Exception
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.ExitExpression)
     */
    public function process($values = [])
    {
        if (empty($values) === true) {
            $values = $this->getCommandLineValues();
        } else {
            $values       = array_merge($this->getDefaults(), $values);
            $this->values = $values;
        }

        if ($values['generator'] !== '') {
            $phpcs = new \PHP_CodeSniffer($values['verbosity']);
            foreach ($values['standard'] as $standard) {
                $phpcs->generateDocs(
                    $standard,
                    $values['sniffs'],
                    $values['generator']
                );
            }

            exit(0);
        }

        // If no standard is supplied, get the default.
        $values['standard'] = $this->validateStandard($values['standard']);
        foreach ($values['standard'] as $standard) {
            if (\PHP_CodeSniffer::isInstalledStandard($standard) === false) {
                // They didn't select a valid coding standard, so help them
                // out by letting them know which standards are installed.
                echo 'ERROR: the "'.$standard.'" coding standard is not installed. ';
                $this->printInstalledStandards();
                exit(2);
            }
        }

        if ($values['explain'] === true) {
            foreach ($values['standard'] as $standard) {
                $this->explainStandard($standard);
            }

            exit(0);
        }

        $fileContents = '';
        if (empty($values['files']) === true) {
            // Check if they are passing in the file contents.
            $handle       = fopen('php://stdin', 'r');
            $fileContents = stream_get_contents($handle);
            fclose($handle);

            if ($fileContents === '') {
                // No files and no content passed in.
                echo 'ERROR: You must supply at least one file or directory to process.'.PHP_EOL.PHP_EOL;
                $this->printUsage();
                exit(2);
            }
        }

        $phpcs = new \PHP_CodeSniffer(
            $values['verbosity'],
            $values['tabWidth'],
            $values['encoding'],
            $values['interactive']
        );
        // This action is the main purpose of creation of this line,
        // see details in description of the class
        $phpcs->allowedFileExtensions += [self::LESS_FILE_EXTENSION => self::LESS_TOKENIZER];

        // Set file extensions if they were specified. Otherwise,
        // let PHP_CodeSniffer decide on the defaults.
        if (empty($values['extensions']) === false) {
            $phpcs->setAllowedFileExtensions($values['extensions']);
        }

        // Set ignore patterns if they were specified.
        if (empty($values['ignored']) === false) {
            $phpcs->setIgnorePatterns($values['ignored']);
        }

        // Set some convenience member vars.
        if ($values['errorSeverity'] === null) {
            $this->errorSeverity = PHPCS_DEFAULT_ERROR_SEV;
        } else {
            $this->errorSeverity = $values['errorSeverity'];
        }

        if ($values['warningSeverity'] === null) {
            $this->warningSeverity = PHPCS_DEFAULT_WARN_SEV;
        } else {
            $this->warningSeverity = $values['warningSeverity'];
        }

        if (empty($values['reports']) === true) {
            $this->values['reports']['full'] = $values['reportFile'];
        }

        $phpcs->setCli($this);

        $phpcs->process(
            $values['files'],
            $values['standard'],
            $values['sniffs'],
            $values['local']
        );

        if ($fileContents !== '') {
            $phpcs->processFile('STDIN', $fileContents);
        }

        // Interactive runs don't require a final report and it doesn't really
        // matter what the retun value is because we know it isn't being read
        // by a script.
        if ($values['interactive'] === true) {
            return 0;
        }

        return $this->printErrorReport(
            $phpcs,
            $values['reports'],
            $values['showSources'],
            $values['reportFile'],
            $values['reportWidth']
        );
    }
}
