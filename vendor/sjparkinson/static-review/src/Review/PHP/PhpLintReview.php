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

class PhpLintReview extends AbstractReview
{
    /**
     * Determins if a given file should be reviewed.
     *
     * @param  FileInterface $file
     * @return bool
     */
    public function canReview(FileInterface $file)
    {
        $extension = $file->getExtension();

        return ($extension === 'php' || $extension === 'phtml');
    }

    /**
     * Checks PHP files using the builtin PHP linter, `php -l`.
     */
    public function review(ReporterInterface $reporter, FileInterface $file)
    {
        $cmd = sprintf('php --syntax-check %s', $file->getFullPath());

        $process = $this->getProcess($cmd);
        $process->run();

        // Create the array of outputs and remove empty values.
        $output = array_filter(explode(PHP_EOL, $process->getOutput()));

        $needle = 'Parse error: syntax error, ';

        if (! $process->isSuccessful()) {

            foreach (array_slice($output, 0, count($output) - 1) as $error) {
                $raw = ucfirst(substr($error, strlen($needle)));
                $message = str_replace(' in ' . $file->getFullPath(), '', $raw);
                $reporter->error($message, $this, $file);
            }

        }
    }
}
