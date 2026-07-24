<?php
/**
 * Finding: compression default. Validates that cache compression is OPT-IN again:
 *   - compress_data unset  -> disabled
 *   - compress_data '0'/0  -> disabled
 *   - compress_data '1'/1  -> enabled
 *
 * Uses reflection to call Factory::isCompressionEnabled directly, so it does not depend on any
 * particular env.php wiring.
 *
 * Run: php dev/tests/acp2e-5132/test1_compression.php
 *
 * Copyright 2026 Adobe. All Rights Reserved.
 */
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

use Magento\Framework\App\Cache\Frontend\Factory;

$factory = acp_om()->create(Factory::class);
$ref = new ReflectionMethod($factory, 'isCompressionEnabled');
$ref->setAccessible(true);

$cases = [
    'unset'        => [[],                          false],
    "string '0'"   => [['compress_data' => '0'],    false],
    'int 0'        => [['compress_data' => 0],       false],
    "string 'false'" => [['compress_data' => 'false'], false],
    "string '1'"   => [['compress_data' => '1'],    true],
    'int 1'        => [['compress_data' => 1],        true],
];

$allOk = true;
foreach ($cases as $label => [$opts, $expected]) {
    $actual = (bool)$ref->invoke($factory, $opts);
    $ok = $actual === $expected;
    $allOk = $allOk && $ok;
    acp_result(
        "compression:$label",
        $ok,
        sprintf('expected=%s actual=%s', $expected ? 'on' : 'off', $actual ? 'on' : 'off')
    );
}

echo $allOk ? "\nTEST 1 PASS\n" : "\nTEST 1 FAIL\n";
exit($allOk ? 0 : 1);
