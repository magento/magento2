<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\PhpStan\Formatters;

use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;
use PHPStan\File\FuzzyRelativePathHelper;
use PHPStan\ShouldNotHappenException;
use PHPStan\Testing\ErrorFormatterTestCase;

/**
 * Tests filter error formatter.
 */
class FilteredErrorFormatterTest extends ErrorFormatterTestCase
{
    protected const DIRECTORY_PATH = __DIR__ . '/Fixtures';

    /**
     * Tests errors filtering.
     *
     * @param string $message
     * @param int $exitCode
     * @param array $fileErrors
     * @param string $expected
     * @throws ShouldNotHappenException
     *
     * @dataProvider dataFormatterOutputProvider
     */
    public function testFormatErrors(
        string $message,
        int $exitCode,
        array $fileErrors,
        string $expected
    ): void {
        $formatter = new FilteredErrorFormatter(
            new FuzzyRelativePathHelper(self::DIRECTORY_PATH, [], '/'),
            false,
            false,
            false,
            true
        );

        $analysisResult = new AnalysisResult(
            $fileErrors,
            [],
            [],
            false,
            false,
            null
        );

        $this->assertSame(
            $exitCode,
            $formatter->formatErrors(
                $analysisResult,
                $this->getOutput()
            ),
            sprintf('%s: response code do not match', $message)
        );
        $this->assertEquals(
            $expected,
            $this->getOutputContent(),
            sprintf('%s: output do not match', $message)
        );
    }

    /**
     * @return array
     */
    public function dataFormatterOutputProvider(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        $errorMessage = 'Method Magento\PhpStan\Formatters\Fixtures\ClassWithIgnoreAnnotation::testMethod() invoked with 2 parameters, 1 required.';
        // phpcs:enable Generic.Files.LineLength.TooLong

        return [
            [
                'No errors',
                0,
                [],
                "\n [OK] No errors\n\n",
            ],
            [
                'All errors are suppressed by ignore annotations',
                0,
                [
                    new Error(
                        'Method level error',
                        self::DIRECTORY_PATH . '/ClassWithIgnoreAnnotation.php',
                        22
                    ),
                    new Error(
                        $errorMessage,
                        self::DIRECTORY_PATH . '/ClassWithIgnoreAnnotation.php',
                        25
                    ),
                    new Error(
                        $errorMessage,
                        self::DIRECTORY_PATH . '/ClassWithIgnoreAnnotation.php',
                        28
                    ),
                    new Error(
                        $errorMessage,
                        self::DIRECTORY_PATH . '/ClassWithIgnoreAnnotation.php',
                        31
                    ),
                    new Error(
                        $errorMessage,
                        self::DIRECTORY_PATH . '/ClassWithIgnoreAnnotation.php',
                        33
                    ),
                ],
                "\n [OK] No errors\n\n",
            ],
            [
                'Errors aren\'t suppressed by ignore annotations',
                1,
                [
                    new Error(
                        $errorMessage,
                        self::DIRECTORY_PATH . '/ClassWithoutIgnoreAnnotation.php',
                        21
                    ),
                ],
                // phpcs:disable Generic.Files.LineLength.TooLong
                ' ------ ---------------------------------------------------------------------------------------------------------------------------
  Line   ClassWithoutIgnoreAnnotation.php
 ------ ---------------------------------------------------------------------------------------------------------------------------
  21     Method Magento\PhpStan\Formatters\Fixtures\ClassWithIgnoreAnnotation::testMethod() invoked with 2 parameters, 1 required.
 ------ ---------------------------------------------------------------------------------------------------------------------------

 [ERROR] Found 1 error

',
                // phpcs:enable Generic.Files.LineLength.TooLong
            ]
        ];
    }
}
