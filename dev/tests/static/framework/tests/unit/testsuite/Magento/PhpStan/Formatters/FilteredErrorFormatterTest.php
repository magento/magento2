<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\PhpStan\Formatters;

use PHPStan\Analyser\Error;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\ErrorsConsoleStyle;
use PHPStan\File\FuzzyRelativePathHelper;
use PHPStan\ShouldNotHappenException;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Class FilteredErrorFormatterTest
 */
class FilteredErrorFormatterTest extends \PHPUnit\Framework\TestCase
{
    private const DIRECTORY_PATH = __DIR__ . '/Fixtures';

    /** @var StreamOutput */
    private $outputStream;

    /** @var ErrorsConsoleStyle */
    private $errorConsoleStyle;

    /**
     * @throws ShouldNotHappenException
     */
    protected function setUp(): void
    {
        $resource = fopen('php://memory', 'w', false);
        if ($resource === false) {
            throw new ShouldNotHappenException();
        }
        $this->outputStream = new StreamOutput($resource);
        $this->errorConsoleStyle = new ErrorsConsoleStyle(new StringInput(''), $this->outputStream);
    }

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
            new FuzzyRelativePathHelper(self::DIRECTORY_PATH, '/', []),
            false,
            false,
            false
        );

        $analysisResult = new AnalysisResult(
            $fileErrors,
            [],
            false,
            self::DIRECTORY_PATH,
            false,
            null
        );

        $this->assertSame(
            $exitCode,
            $formatter->formatErrors(
                $analysisResult,
                $this->errorConsoleStyle
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
        // phpcs:disable Magento2.Files.LineLength.MaxExceeded
        $errorMessage = 'Method Magento\PhpStan\Formatters\Fixtures\ClassWithIgnoreAnnotation::testMethod() invoked with 2 parameters, 1 required.';
        // phpcs:enable Magento2.Files.LineLength.MaxExceeded

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
                // phpcs:disable Magento2.Files.LineLength.MaxExceeded
                ' ------ ---------------------------------------------------------------------------------------------------------------------------
  Line   ClassWithoutIgnoreAnnotation.php
 ------ ---------------------------------------------------------------------------------------------------------------------------
  21     Method Magento\PhpStan\Formatters\Fixtures\ClassWithIgnoreAnnotation::testMethod() invoked with 2 parameters, 1 required.
 ------ ---------------------------------------------------------------------------------------------------------------------------

 [ERROR] Found 1 error

',
                // phpcs:enable Magento2.Files.LineLength.MaxExceeded
            ]
        ];
    }

    /**
     * @return string
     * @throws ShouldNotHappenException
     */
    private function getOutputContent(): string
    {
        rewind($this->outputStream->getStream());
        $contents = stream_get_contents($this->outputStream->getStream());
        if ($contents === false) {
            throw new ShouldNotHappenException();
        }

        return $this->rtrimMultiline($contents);
    }

    /**
     * @param string $output
     * @return string
     */
    private function rtrimMultiline(string $output): string
    {
        $result = array_map(
            function (string $line): string {
                return rtrim($line, " \r\n");
            },
            explode("\n", $output)
        );

        return implode("\n", $result);
    }
}
