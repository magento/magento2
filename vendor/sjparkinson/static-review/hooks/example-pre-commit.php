#!/usr/bin/env php
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

$included = include file_exists(__DIR__ . '/../vendor/autoload.php')
    ? __DIR__ . '/../vendor/autoload.php'
    : __DIR__ . '/../../../autoload.php';

if (! $included) {
    echo 'You must set up the project dependencies, run the following commands:' . PHP_EOL
       . 'curl -sS https://getcomposer.org/installer | php' . PHP_EOL
       . 'php composer.phar install' . PHP_EOL;

    exit(1);
}

// Reference the required classes and the reviews you want to use.
use StaticReview\Reporter\Reporter;
use StaticReview\Review\General\LineEndingsReview;
use StaticReview\StaticReview;
use StaticReview\VersionControl\GitVersionControl;

$reporter = new Reporter();
$review   = new StaticReview($reporter);

// Add any reviews to the StaticReview instance, supports a fluent interface.
$review->addReview(new LineEndingsReview());

$git = new GitVersionControl();

// Review the staged files.
$review->review($git->getStagedFiles());

echo PHP_EOL;

// Check if any issues were found.
// Exit with a non-zero to block the commit.
($reporter->hasIssues()) ? exit(1) : exit(0);
