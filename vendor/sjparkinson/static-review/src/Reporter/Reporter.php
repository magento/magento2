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

use StaticReview\Collection\IssueCollection;
use StaticReview\File\FileInterface;
use StaticReview\Issue\Issue;
use StaticReview\Review\ReviewInterface;

class Reporter implements ReporterInterface
{
    protected $issues;

    /**
     * Initializes a new instance of the Reporter class.
     *
     * @param  IssueCollection $issues
     * @return Reporter
     */
    public function __construct()
    {
        $this->issues = new IssueCollection();
    }

    public function progress($current, $total)
    {
        echo sprintf("Reviewing file %d of %d.\r", $current, $total);
    }

    /**
     * Reports an Issue raised by a Review.
     *
     * @param  int             $level
     * @param  string          $message
     * @param  ReviewInterface $review
     * @param  FileInterface   $file
     * @return Reporter
     */
    public function report($level, $message, ReviewInterface $review, FileInterface $file)
    {
        $issue = new Issue($level, $message, $review, $file);

        $this->issues->append($issue);

        return $this;
    }

    /**
     * Reports an Info Issue raised by a Review.
     *
     * @param  string          $message
     * @param  ReviewInterface $review
     * @param  FileInterface   $file
     * @return Reporter
     */
    public function info($message, ReviewInterface $review, FileInterface $file)
    {
        $this->report(Issue::LEVEL_INFO, $message, $review, $file);

        return $this;
    }

    /**
     * Reports an Warning Issue raised by a Review.
     *
     * @param  string          $message
     * @param  ReviewInterface $review
     * @param  FileInterface   $file
     * @return Reporter
     */
    public function warning($message, ReviewInterface $review, FileInterface $file)
    {
        $this->report(Issue::LEVEL_WARNING, $message, $review, $file);

        return $this;
    }

    /**
     * Reports an Error Issue raised by a Review.
     *
     * @param  string          $message
     * @param  ReviewInterface $review
     * @param  FileInterface   $file
     * @return Reporter
     */
    public function error($message, ReviewInterface $review, FileInterface $file)
    {
        $this->report(Issue::LEVEL_ERROR, $message, $review, $file);

        return $this;
    }

    /**
     * Checks if the reporter has revieved any Issues.
     *
     * @return IssueCollection
     */
    public function hasIssues()
    {
        return (count($this->issues) > 0);
    }

    /**
     * Gets the reporters IssueCollection.
     *
     * @return IssueCollection
     */
    public function getIssues()
    {
        return $this->issues;
    }
}
