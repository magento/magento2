<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sniffs\Less;

use Magento\TestFramework\CodingStandard\Tool\CodeSniffer\LessWrapper;

class AvoidIdSniffTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return array
     */
    public function processDataProvider()
    {
        return [
            [
                'avoid-ids.less',
                'avoid-ids-errors.txt'
            ]
        ];
    }

    /**
     * @param string $fileUnderTest
     * @param string $expectedReportFile
     * @dataProvider processDataProvider
     */
    public function testProcess($fileUnderTest, $expectedReportFile)
    {
        $reportFile = __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'phpcs_report.txt';
        $wrapper = new LessWrapper();
        $codeSniffer = new \Magento\TestFramework\CodingStandard\Tool\CodeSniffer(
            'Magento',
            $reportFile,
            $wrapper
        );
        $codeSniffer->setExtensions([LessWrapper::LESS_FILE_EXTENSION]);
        $result = $codeSniffer->run(
            [__DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . $fileUnderTest]
        );
        // Remove the absolute path to the file from the output
        $actual = preg_replace('/^.+\n/', '', ltrim(file_get_contents($reportFile)));
        $expected = file_get_contents(
            __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . $expectedReportFile
        );
        unlink($reportFile);
        $this->assertEquals(1, $result);
        $this->assertEquals($expected, $actual);
    }
}
