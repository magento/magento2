<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * PHP Coding Standards fixer configuration
 */

$finder = PhpCsFixer\Finder::create()
    ->name('*.phtml')
    ->exclude('dev/tests/integration/tmp')
    ->exclude('dev/tests/integration/var')
    ->exclude('lib/internal/Cm')
    ->exclude('lib/internal/Credis')
    ->exclude('lib/internal/Less')
    ->exclude('lib/internal/LinLibertineFont')
    ->exclude('pub/media')
    ->exclude('pub/static')
    ->exclude('setup/vendor')
    ->exclude('var');

$config = new PhpCsFixer\Config();
$config->setFinder($finder)
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'include' => true,
        'new_with_braces' => true,
        'no_empty_statement' => true,
        'no_extra_blank_lines' => true,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'multiline_whitespace_before_semicolons' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_trailing_comma_in_singleline_array' => true,
        'no_unused_imports' => true,
        'no_whitespace_in_blank_line' => true,
        'object_operator_without_whitespace' => true,
        'ordered_imports' => true,
        'standardize_not_equals' => true,
        'ternary_operator_spaces' => true,
    ]);
return $config;
