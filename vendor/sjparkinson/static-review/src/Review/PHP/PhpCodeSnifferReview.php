<?php

/*
 * This file is part of StaticReview
 *
 * Copyright (c) 2014 Samuel Parkinson <@samparkinson_>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see http://github.com/sjparkinson/static-review/blob/master/LICENSE.md
 */

namespace StaticReview\Review\PHP;

use StaticReview\File\FileInterface;
use StaticReview\Reporter\ReporterInterface;
use StaticReview\Review\AbstractReview;

class PhpCodeSnifferReview extends AbstractReview
{
    protected $options = [];

    /**
     * Gets the value of an option.
     *
     * @param  string $option
     * @return string
     */
    public function getOption($option)
    {
        return $this->options[$option];
    }

    /**
     * Gets a string of the set options to pass to the command line.
     *
     * @return string
     */
    public function getOptionsForConsole()
    {
        $builder = '';

        foreach ($this->options as $option => $value) {
            $builder .= '--' . $option;

            if ($value) {
                $builder .= '=' . $value;
            }

            $builder .= ' ';
        }

        return $builder;
    }

    /**
     * Adds an option to be included when running PHP_CodeSniffer. Overwrites the values of options with the same name.
     *
     * @param  string               $option
     * @param  string               $value
     * @return PhpCodeSnifferReview
     */
    public function setOption($option, $value)
    {
        if ($option === 'report') {
            throw new \RuntimeException('"report" is not a valid option name.');
        }

        $this->options[$option] = $value;

        return $this;
    }

    /**
     * Determins if a given file should be reviewed.
     *
     * @param  FileInterface $file
     * @return bool
     */
    public function canReview(FileInterface $file)
    {
        return ($file->getExtension() === 'php');
    }

    /**
     * Checks PHP files using PHP_CodeSniffer.
     */
    public function review(ReporterInterface $reporter, FileInterface $file)
    {
        $cmd = 'vendor/bin/phpcs --report=json ';

        if ($this->getOptionsForConsole()) {
            $cmd .= $this->getOptionsForConsole();
        }

        $cmd .= $file->getFullPath();

        $process = $this->getProcess($cmd);
        $process->run();

        if (! $process->isSuccessful()) {

            // Create the array of outputs and remove empty values.
            $output = json_decode($process->getOutput(), true);

            $filter = function ($acc, $file) {
                if ($file['errors'] > 0 || $file['warnings'] > 0) {
                    return $acc + $file['messages'];
                }
            };

            foreach (array_reduce($output['files'], $filter, []) as $error) {
                $message = $error['message'] . ' on line ' . $error['line'];
                $reporter->warning($message, $this, $file);
            }
        }
    }
}
