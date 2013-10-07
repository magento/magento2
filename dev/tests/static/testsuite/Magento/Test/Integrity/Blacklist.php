<?php
/**
 * Files excluded from the integrity test for PSR-X standards
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an e-mail
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     static
 * @subpackage  Integrity
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
$i18nFixtureDir = '/dev/tests/integration/testsuite/Magento/Test/Tools/I18n/Code/Dictionary/_files/';
$i18nLibDir = '/dev/tools/Magento/Tools/I18n/Zend/';
$phpExemplar = '/dev/tests/static/testsuite/Magento/Test/Php/Exemplar/';

return array(
    '/dev/tests/unit/testsuite/Magento/Test/Tools/Di/_files/app/code/Magento/SomeModule/Model/Test.php',
    '/dev/tests/unit/testsuite/Magento/Test/Tools/Di/_files/app/code/Magento/SomeModule/Helper/Test.php',
    $i18nLibDir . 'Exception.php',
    $i18nLibDir . 'Console/Getopt/Exception.php',
    $i18nLibDir . 'Console/Getopt.php',
    $i18nFixtureDir . 'source/app/code/Magento/FirstModule/Model/Model.php',
    $i18nFixtureDir . 'source/app/code/Magento/SecondModule/Model/Model.php',
    $i18nFixtureDir . 'source/unused/Model.php',
    $i18nFixtureDir . 'source/app/code/Magento/FirstModule/Helper/Helper.php',
    $phpExemplar . 'CodeMessTest/phpmd/input/coupling.php',
    $phpExemplar . 'CodeMessTest/phpmd/input/cyclomatic_complexity.php',
    $phpExemplar . 'CodeMessTest/phpmd/input/descendant_count.php',
    $phpExemplar . 'CodeMessTest/phpmd/input/field_count.php',
    $phpExemplar . 'CodeMessTest/phpmd/input/inheritance_depth.php',
    $phpExemplar . 'CodeMessTest/phpmd/input/method_count.php',
    $phpExemplar . 'CodeMessTest/phpmd/input/method_length.php',
    $phpExemplar . 'CodeMessTest/phpmd/input/naming.php',
    $phpExemplar . 'CodeMessTest/phpmd/input/parameter_list.php',
    $phpExemplar . 'CodeMessTest/phpmd/input/prohibited_statement.php',
    $phpExemplar . 'CodeMessTest/phpmd/input/prohibited_statement_goto.php',
    $phpExemplar . 'CodeMessTest/phpmd/input/public_count.php',
    $phpExemplar . 'CodeMessTest/phpmd/input/unused.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/coding_style/classes/brace_same_line.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/coding_style/classes/brace_several_lines_below.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/coding_style/classes/brace_with_code.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/coding_style/classes/brace_with_spaces.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/coding_style/classes/normal_class.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/coding_style/functions/method_without_scope.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/coding_style/inline_doc/normal.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/coding_style/inline_doc/format/wrong_align.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/general/indentation_nonexact_phpdoc.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/constant/minuscule_letter.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/constant/normal_constant.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/method/capital_start.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/method/normal_camelcase.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/method/normal_plain.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/method/normal_underscore_start.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/method/underscore_middle.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/method/underscore_start_public.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/property/capital_start.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/property/normal_camelcase.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/property/normal_plain.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/property/normal_underscore.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/property/underscore_absent.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/property/underscore_middle.php',
    $phpExemplar . 'CodeStyleTest/phpcs/input/naming/property/underscore_start_public.php',
);
