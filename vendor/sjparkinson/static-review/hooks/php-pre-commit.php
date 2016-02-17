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
use League\CLImate\CLImate;
use StaticReview\Reporter\Reporter;
use StaticReview\Review\Composer\ComposerLintReview;
use StaticReview\Review\General\LineEndingsReview;
use StaticReview\Review\General\NoCommitTagReview;
use StaticReview\Review\PHP\PhpLeadingLineReview;
use StaticReview\Review\PHP\PhpLintReview;
use StaticReview\StaticReview;
use StaticReview\VersionControl\GitVersionControl;

$reporter = new Reporter();
$climate  = new CLImate();
$git      = new GitVersionControl();

$review = new StaticReview($reporter);

// Add any reviews to the StaticReview instance, supports a fluent interface.
$review->addReview(new LineEndingsReview())
       ->addReview(new PhpLeadingLineReview())
       ->addReview(new NoCommitTagReview())
       ->addReview(new PhpLintReview())
       ->addReview(new ComposerLintReview());

// Review the staged files.
$review->review($git->getStagedFiles());

// Check if any matching issues were found.
if ($reporter->hasIssues()) {

    $climate->out('')->out('');

    foreach ($reporter->getIssues() as $issue) {
        $climate->red($issue);
    }

    $climate->out('')->red('✘ Please fix the errors above.');

    exit(1);

} else {

    $climate->out('')->green('✔ Looking good.')->white('Have you tested everything?');

    exit(0);
}
