<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Pre-commit hook installation:
 * vendor/bin/static-review.php hook:install dev/tools/Magento/Tools/StaticReview/pre-commit .git/hooks/pre-commit
 */
$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->name('*.phtml')
    ->exclude('dev/tests/functional/generated')
    ->exclude('dev/tests/functional/var')
    ->exclude('dev/tests/functional/vendor')
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

return Symfony\CS\Config\Config::create()
    ->finder($finder)
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers([
        'double_arrow_multiline_whitespaces',
        'duplicate_semicolon',
        'extra_empty_lines',
        'include',
        'join_function',
        'multiline_array_trailing_comma',
        'namespace_no_leading_whitespace',
        'new_with_braces',
        'object_operator',
        'operators_spaces',
        'remove_leading_slash_use',
        'remove_lines_between_uses',
        'single_array_no_trailing_comma',
        'spaces_before_semicolon',
        'standardize_not_equal',
        'ternary_spaces',
        'unused_use',
        'whitespacy_lines',
        'concat_with_spaces',
        'multiline_spaces_before_semicolon',
        'ordered_use',
        'short_array_syntax',
    ]);
