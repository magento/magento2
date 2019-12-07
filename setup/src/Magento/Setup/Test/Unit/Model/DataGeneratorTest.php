<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use Magento\Setup\Model\DataGenerator;

/**
 * Class DataGeneratorTest
 */
class DataGeneratorTest extends \PHPUnit\Framework\TestCase
{

    const PATH_TO_CSV_FILE = '/_files/dictionary.csv';

    /**
     * @test
     *
     * @return void
     */
    public function testGenerate()
    {
        $data = file(__DIR__ . self::PATH_TO_CSV_FILE);
        $wordCount = count($data);
        $model = new DataGenerator(__DIR__ . self::PATH_TO_CSV_FILE);
        $result = $model->generate($wordCount, $wordCount);

        $found = false;
        foreach ($data as $word) {
            $found = (strpos($result, $word[0]) !== false) || $found;
        }
        $this->assertTrue($found);
        $this->assertEquals($wordCount, count(explode(" ", $result)));
    }

    public function testGenerateWithKey()
    {
        $key = 'generate-test';

        $data = file(__DIR__ . self::PATH_TO_CSV_FILE);
        $wordCount = random_int(1, count($data));
        $model = new DataGenerator(__DIR__ . self::PATH_TO_CSV_FILE);
        $result = $model->generate($wordCount, $wordCount, $key);

        $foundResult = $model->generate($wordCount, $wordCount, $key);

        $this->assertEquals($wordCount, count(explode(" ", $result)));
        $this->assertEquals($result, $foundResult);
    }
}
