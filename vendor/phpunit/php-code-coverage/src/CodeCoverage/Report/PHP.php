<?php
/*
 * This file is part of the PHP_CodeCoverage package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Uses var_export() to write a PHP_CodeCoverage object to a file.
 *
 * @since Class available since Release 1.1.0
 */
class PHP_CodeCoverage_Report_PHP
{
    /**
     * @param  PHP_CodeCoverage $coverage
     * @param  string           $target
     * @return string
     */
    public function process(PHP_CodeCoverage $coverage, $target = null)
    {
        $filter = $coverage->filter();

        $output = sprintf(
            '<?php
$coverage = new PHP_CodeCoverage;
$coverage->setData(%s);
$coverage->setTests(%s);

$filter = $coverage->filter();
$filter->setBlacklistedFiles(%s);
$filter->setWhitelistedFiles(%s);

return $coverage;',
            var_export($coverage->getData(true), 1),
            var_export($coverage->getTests(), 1),
            var_export($filter->getBlacklistedFiles(), 1),
            var_export($filter->getWhitelistedFiles(), 1)
        );

        if ($target !== null) {
            return file_put_contents($target, $output);
        } else {
            return $output;
        }
    }
}
