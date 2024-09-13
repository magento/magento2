<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PhpStan\Formatters;

use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorFormatter\TableErrorFormatter;
use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\Command\Output;

/**
 * To mute the PHPStan error message add a comment above the reported error line.
 *
 * Example of usage:
 *
 *   // phpstan:ignore "Method Magento\TestModule\TestClass::testMethod() invoked with 1 parameter, 0 required."
 *   $this->testMethod(1);
 *
 * or replace some part of error message with *
 *
 *   // phpstan:ignore "Method * invoked with 1 parameter, 0 required."
 *   $this->testMethod(1);
 *
 * or just
 *
 *   // phpstan:ignore
 *   $this->testMethod(1);
 *
 * or
 *
 *   $this->testMethod(1); // phpstan:ignore
 *
 * The error message will be suppressed.
 *
 * @see \Magento\PhpStan\Formatters\Fixtures\ClassWithIgnoreAnnotation
 */
class FilteredErrorFormatter implements ErrorFormatter
{
    private const MUTE_ERROR_ANNOTATION = 'phpstan:ignore';
    private const NO_ERRORS = 0;

    /**
     * @var TableErrorFormatter
     */
    private $tableErrorFormatter;

    /**
     * @param TableErrorFormatter $tableErrorFormatter
     */
    public function __construct(TableErrorFormatter $tableErrorFormatter)
    {
        $this->tableErrorFormatter = $tableErrorFormatter;
    }

    /**
     * @inheritdoc
     */
    public function formatErrors(AnalysisResult $analysisResult, Output $output): int
    {
        if (!$analysisResult->hasErrors()) {
            $output->getStyle()->success('No errors');
            return self::NO_ERRORS;
        }

        $params = [
            $this->clearIgnoredErrors($analysisResult->getFileSpecificErrors()),
            $analysisResult->getNotFileSpecificErrors(),
            $analysisResult->getInternalErrors(),
            $analysisResult->getWarnings(),
            $analysisResult->getCollectedData(),
            $analysisResult->isDefaultLevelUsed(),
            $analysisResult->getProjectConfigFile(),
            $analysisResult->isResultCacheSaved(),
            $analysisResult->getPeakMemoryUsageBytes(),
            $analysisResult->isResultCacheUsed(),
            $analysisResult->getChangedProjectExtensionFilesOutsideOfAnalysedPaths()
        ];

        $reflectionClass = new \ReflectionClass(AnalysisResult::class);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor->getNumberOfParameters() !== count($params)
            && $reflectionClass->hasMethod('isResultCacheUsed')
        ) {
            $params[] = $analysisResult->isResultCacheUsed();
        }

        //@phpstan:ignore-line
        $clearedAnalysisResult = new AnalysisResult(...$params);

        return $this->tableErrorFormatter->formatErrors($clearedAnalysisResult, $output);
    }

    /**
     * Filters error list.
     *
     * @param array $fileSpecificErrors
     * @return array
     */
    private function clearIgnoredErrors(array $fileSpecificErrors): array
    {
        foreach ($fileSpecificErrors as $index => $error) {
            $fileName = $error->getFile();
            // phpcs:ignore Magento2.Functions.DiscouragedFunction
            if (!file_exists($fileName)) {
                continue;
            }

            $line = $error->getLine() ? $this->getLineWithMuteErrorAnnotation($error->getLine(), $fileName) : null;
            if ($line === null) {
                continue;
            }

            $extractErrorPattern = '@' . self::MUTE_ERROR_ANNOTATION . '\s+"(.*?)"@';
            $errorPattern = preg_match($extractErrorPattern, $line, $result) ? $this->preparePattern($result[1]) : '';
            if ($errorPattern && !preg_match('@' . $errorPattern . '@i', $error->getMessage())) {
                continue;
            }

            unset($fileSpecificErrors[$index]);
        }

        return $fileSpecificErrors;
    }

    /**
     * Returns context of the line with mute error annotation.
     *
     * @param int $errorLine
     * @param string $fileName
     * @return string|null
     */
    private function getLineWithMuteErrorAnnotation(int $errorLine, string $fileName): ?string
    {
        $file = new \SplFileObject($fileName);
        $lineNumbersToCheck = [
            $errorLine - 2, // the line above to the line that caused the error
            $errorLine - 1, // the line that caused the error
            $errorLine - 3, // the line two lines above to the line that caused the error
        ];

        foreach ($lineNumbersToCheck as $lineNumber) {
            $file->seek($lineNumber > 0 ? $lineNumber : 0);
            $line = $file->current();
            if (strpos($line, self::MUTE_ERROR_ANNOTATION) !== false) {
                return $line;
            }
        }

        return null;
    }

    /**
     * Prepares error pattern.
     *
     * @param string $errorDescription
     * @return string
     */
    private function preparePattern(string $errorDescription)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return str_replace('*', '(?:.*?)', addcslashes(trim($errorDescription), '\()[]'));
    }
}
