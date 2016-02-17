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

namespace StaticReview\Reporter;

use StaticReview\File\FileInterface;
use StaticReview\Review\ReviewInterface;

interface ReporterInterface
{
    public function report($level, $message, ReviewInterface $review, FileInterface $file);

    public function info($message, ReviewInterface $review, FileInterface $file);

    public function warning($message, ReviewInterface $review, FileInterface $file);

    public function error($message, ReviewInterface $review, FileInterface $file);

    public function hasIssues();

    public function getIssues();
}
