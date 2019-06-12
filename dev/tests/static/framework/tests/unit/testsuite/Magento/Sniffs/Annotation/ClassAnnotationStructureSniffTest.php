<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
namespace Magento\Sniffs\Annotation;

class ClassAnnotationStructureSniffTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            [
                'ClassAnnotationFixture.php',
                'class_annotation_errors.txt',
            ],
            [
                'AbstractClassAnnotationFixture.php',
                'abstract_class_annotation_errors.txt',
            ],
            [
                'ClassAnnotationNoShortDescriptionFixture.php',
                'class_annotation_noshortdescription_errors.txt',
            ],
            [
                'ClassAnnotationNoSpacingBetweenLinesFixture.php',
                'class_annotation_nospacingbetweenLines_errors.txt',
            ]
        ];
    }

    /**
     * Copy a file
     *
     * @param string $source
     * @param string $destination
     */
    private function copyFile($source, $destination) : void
    {
        $sourcePath = $source;
        $destinationPath = $destination;
        $sourceDirectory = opendir($sourcePath);
        while ($readFile = readdir($sourceDirectory)) {
            if ($readFile != '.' && $readFile != '..') {
                if (!file_exists($destinationPath . $readFile)) {
                    copy($sourcePath . $readFile, $destinationPath . $readFile);
                }
            }
        }
        closedir($sourceDirectory);
    }

    /**
     * @param string $fileUnderTest
     * @param string $expectedReportFile
     * @dataProvider processDataProvider
     */
    public function testProcess($fileUnderTest, $expectedReportFile)
    {
        $reportFile = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'phpcs_report.txt';
        $this->copyFile(
            __DIR__ . DIRECTORY_SEPARATOR . '_files'. DIRECTORY_SEPARATOR,
            TESTS_TEMP_DIR
        );
        $codeSniffer = new \Magento\TestFramework\CodingStandard\Tool\CodeSniffer(
            'Magento',
            $reportFile,
            new \Magento\TestFramework\CodingStandard\Tool\CodeSniffer\Wrapper()
        );
        $result = $codeSniffer->run(
            [TESTS_TEMP_DIR . $fileUnderTest]
        );
        $actual = file_get_contents($reportFile);
        $expected = file_get_contents(
            TESTS_TEMP_DIR . $expectedReportFile
        );
        unlink($reportFile);
        $this->assertEquals(2, $result);
        $this->assertEquals($expected, $actual);
    }
}
