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

namespace StaticReview\Review;

use StaticReview\File\FileInterface;
use StaticReview\Reporter\ReporterInterface;

interface ReviewInterface
{
    public function canReview(FileInterface $file);

    public function review(ReporterInterface $reporter, FileInterface $file);
}
