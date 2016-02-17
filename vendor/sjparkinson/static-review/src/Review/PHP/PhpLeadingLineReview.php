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
use Symfony\Component\Process\Process;

class PhpLeadingLineReview extends AbstractReview
{
    /**
     * Determins if the given file should be revewed.
     *
     * @param  FileInterface $file
     * @return bool
     */
    public function canReview(FileInterface $file)
    {
        return ($file->getExtension() === 'php');
    }

    /**
     * Checks if the set file starts with the correct character sequence, which
     * helps to stop any rouge whitespace making it in before the first php tag.
     *
     * @link http://stackoverflow.com/a/2440685
     */
    public function review(ReporterInterface $reporter, FileInterface $file)
    {
        $cmd = sprintf('read -r LINE < %s && echo $LINE', $file->getFullPath());

        $process = $this->getProcess($cmd);
        $process->run();

        if (! in_array(trim($process->getOutput()), ['<?php', '#!/usr/bin/env php'])) {

            $message = 'File must begin with `<?php` or `#!/usr/bin/env php`';
            $reporter->error($message, $this, $file);

        }
    }
}
