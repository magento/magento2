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

namespace StaticReview\Review\General;

use StaticReview\File\FileInterface;
use StaticReview\Reporter\ReporterInterface;
use StaticReview\Review\AbstractReview;
use Symfony\Component\Process\Process;

class LineEndingsReview extends AbstractReview
{
    /**
     * Review any text based file.
     *
     * @link http://stackoverflow.com/a/632786
     *
     * @param  FileInterface $file
     * @return bool
     */
    public function canReview(FileInterface $file)
    {
        $mime = $file->getMimeType();

        // check to see if the mime-type starts with 'text'
        return (substr($mime, 0, 4) === 'text');
    }

    /**
     * Checks if the set file contains any CRLF line endings.
     *
     * @link http://stackoverflow.com/a/3570574
     */
    public function review(ReporterInterface $reporter, FileInterface $file)
    {
        $cmd = sprintf('file %s | grep --fixed-strings --quiet "CRLF"', $file->getFullPath());

        $process = $this->getProcess($cmd);
        $process->run();

        if ($process->isSuccessful()) {

            $message = 'File contains CRLF line endings';
            $reporter->error($message, $this, $file);

        }
    }
}
