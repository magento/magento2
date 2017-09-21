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
namespace Magento\Tools\StaticReview;

use StaticReview\File\FileInterface;
use StaticReview\Reporter\ReporterInterface;
use StaticReview\Review\AbstractReview;

class PhpCsFixerReview extends AbstractReview
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->options = $options;
    }

    /**
     * Obtained from .php_cs configuration file.
     *
     * @param  FileInterface $file
     * @return bool
     */
    public function canReview(FileInterface $file)
    {
        return in_array($file->getExtension(), ['php', 'phtml', 'xml', 'yml']);
    }

    /**
     * Checks and fixes PHP files using PHP Coding Standards Fixer.
     *
     * @param ReporterInterface $reporter
     * @param FileInterface $file
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function review(ReporterInterface $reporter, FileInterface $file)
    {
        $cmd = 'vendor/bin/php-cs-fixer -vvv ';
        foreach ($this->options as $key => $value) {
            $cmd .= ' --' . $key . '=' . escapeshellarg($value);
        }
        $cmd .= ' fix ' . escapeshellarg($file->getRelativePath());

        $process = $this->getProcess($cmd);
        $process->run();

        $process = $this->getProcess('git add ' . escapeshellarg($file->getRelativePath()));
        $process->run();
    }
}
