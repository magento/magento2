<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Filter\DirectiveProcessor\Filter;

use Magento\Framework\App\ObjectManager;
use PHPUnit\Framework\TestCase;

class FilterApplierTest extends TestCase
{
    /**
     * @var FilterApplier
     */
    private $applier;

    protected function setUp(): void
    {
        $this->applier = ObjectManager::getInstance()->get(FilterApplier::class);
    }

    /**
     * @dataProvider arrayUseCaseProvider
     */
    public function testArrayUseCases($param, $input, $expected)
    {
        $result = $this->applier->applyFromArray($param, $input);

        self::assertSame($expected, $result);
    }

    public function arrayUseCaseProvider()
    {
        $standardInput = 'Hello ' . "\n" . ' &world!';
        return [
            'raw' => [['raw'], $standardInput, $standardInput],
            'standard usage' => [['escape', 'nl2br'], $standardInput, 'Hello <br />' . "\n" . ' &amp;world!'],
            'single usage' => [['escape'], $standardInput, 'Hello ' . "\n" . ' &amp;world!'],
            'params' => [
                ['nl2br', 'escape:url', 'foofilter'],
                $standardInput,
                '12%DLROW62%02%A0%E3%F2%02%RBC3%02%OLLEH'
            ],
            'no filters' => [[], $standardInput, $standardInput],
            'bad filters' => [['', false, 0, null], $standardInput, $standardInput],
            'mixed filters' => [['', false, 'escape', 0, null], $standardInput, 'Hello ' . "\n" . ' &amp;world!'],
        ];
    }

    /**
     * @dataProvider rawUseCaseProvider
     */
    public function testRawUseCases($param, $input, $expected)
    {
        $result = $this->applier->applyFromRawParam($param, $input, ['escape']);

        self::assertSame($expected, $result);
    }

    public function rawUseCaseProvider()
    {
        $standardInput = 'Hello ' . "\n" . ' &world!';
        return [
            'raw' => ['|raw', $standardInput, $standardInput],
            'standard usage' => ['|escape|nl2br', $standardInput, 'Hello <br />' . "\n" . ' &amp;world!'],
            'single usage' => ['|escape', $standardInput, 'Hello ' . "\n" . ' &amp;world!'],
            'default filters' => ['', $standardInput, 'Hello ' . "\n" . ' &amp;world!'],
            'params' => ['|nl2br|escape:url|foofilter', $standardInput, '12%DLROW62%02%A0%E3%F2%02%RBC3%02%OLLEH'],
        ];
    }
}
