<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-03-31 12:41:27
 */

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

if (!function_exists('import_url')) {
    function import_url($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
}

date_default_timezone_set('Asia/Ho_Chi_Minh');

try {
    $configPath    = 'https://raw.githubusercontent.com/diepxuan/php/main/.php-cs-fixer.dist.php';
    $configContent = import_url($configPath);
    $tempFile      = sys_get_temp_dir() . '/.php-cs-fixer.dist.php';
    file_put_contents($tempFile, $configContent);
    return require $tempFile;
    // $config = import_url($configPath);
    // $config = str_replace('<?php', '', $config);
} catch (Throwable $e) {
    return (new Config())
        ->setRiskyAllowed(true)
        ->setRules([
            '@PHP74Migration'            => true,
            '@PHP74Migration:risky'      => true,
            '@PHPUnit100Migration:risky' => true,
            '@PhpCsFixer'                => true,
            '@PhpCsFixer:risky'          => true,
            // '@PER-CS2.0'                       => true,
            'general_phpdoc_annotation_remove' => ['annotations' => ['expectedDeprecation']], // one should use PHPUnit built-in method instead
            'header_comment'                   => ['header' => sprintf(<<<'EOF'
                @copyright  © 2019 Dxvn, Inc.

                @author     Tran Ngoc Duc <ductn@diepxuan.com>
                @author     Tran Ngoc Duc <caothu91@gmail.com>

                @lastupdate %s
                EOF, date('Y-m-d H:i:s'))],

            'modernize_strpos'            => true, // needs PHP 8+ or polyfill
            'no_useless_concat_operator'  => false, // TODO switch back on when the `src/Console/Application.php` no longer needs the concat
            'numeric_literal_separator'   => true,
            'string_implicit_backslashes' => true, // https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/pull/7786

            '@PSR2'              => true,
            'array_syntax'       => ['syntax' => 'short'],
            'concat_space'       => ['spacing' => 'one'],
            'include'            => true,
            'new_with_braces'    => true,
            'no_empty_statement' => true,
            // 'no_extra_consecutive_blank_lines' => true,
            'no_leading_import_slash'                     => true,
            'no_leading_namespace_whitespace'             => true,
            'no_multiline_whitespace_around_double_arrow' => true,
            // 'multiline_whitespace_before_semicolons' => false,
            'no_singleline_whitespace_before_semicolons' => true,
            'no_trailing_comma_in_singleline_array'      => true,
            'no_alias_language_construct_call'           => true,
            'assign_null_coalescing_to_coalesce_equal'   => true,
            'no_unused_imports'                          => true,
            'no_whitespace_in_blank_line'                => true,
            'object_operator_without_whitespace'         => true,
            'ordered_imports'                            => true,
            'standardize_not_equals'                     => true,
            'ternary_operator_spaces'                    => true,
            'binary_operator_spaces'                     => [
                'default'   => 'at_least_single_space',
                'operators' => [
                    '=>' => 'align_single_space_minimal',
                    '='  => 'align_single_space_minimal',
                ]],
            'operator_linebreak' => ['only_booleans' => true],
        ])
        ->setFinder(
            (new Finder())
                ->ignoreDotFiles(false)
                ->ignoreVCSIgnored(true)
                ->exclude([
                    'dev-tools/phpstan',
                    'tests/Fixtures',
                    'lib/internal/Magento/Framework',
                    'app/code/Magento',
                ])
                ->in(__DIR__)
        )
    ;
}
