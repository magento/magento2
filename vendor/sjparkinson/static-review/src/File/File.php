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

namespace StaticReview\File;

class File implements FileInterface
{
    const STATUS_ADDED    = 'A';

    const STATUS_COPIED   = 'C';

    const STATUS_MODIFIED = 'M';

    const STATUS_RENAMED  = 'R';

    /**
     * The full path to the file.
     */
    private $filePath;

    /**
     * The files status.
     */
    private $fileStatus;

    /**
     * The projects base directory.
     */
    private $projectPath;

    /**
     * The cached location of the file.
     */
    private $cachedPath;

    /**
     * Initializes a new instance of the File class.
     *
     * @param string $fileStatus
     * @param string $filePath
     * @param string $projectPath
     */
    public function __construct(
        $fileStatus,
        $filePath,
        $projectPath
    ) {
        $this->fileStatus  = $fileStatus;
        $this->filePath    = $filePath;
        $this->projectPath = $projectPath;
    }

    /**
     * Returns the name of the file including its extension.
     *
     * @return string
     */
    public function getFileName()
    {
        return basename($this->filePath);
    }

    /**
     * Returns the local path to the file from the base of the git repository.
     *
     * @return string
     */
    public function getRelativePath()
    {
        return str_replace($this->projectPath . DIRECTORY_SEPARATOR, '', $this->filePath);
    }

    /**
     * Returns the full path to the file.
     *
     * @return string
     */
    public function getFullPath()
    {
        if (file_exists($this->getCachedPath())) {
            return $this->getCachedPath();
        }

        return $this->filePath;
    }

    /**
     * Returns the path to the cached copy of the file.
     *
     * @return string
     */
    public function getCachedPath()
    {
        return $this->cachedPath;
    }

    /**
     * Sets the path to the cached copy of the file.
     *
     * @param  string $path
     * @return File
     */
    public function setCachedPath($path)
    {
        $this->cachedPath = $path;

        return $this;
    }

    /**
     * Returns the files extension.
     *
     * @return string
     */
    public function getExtension()
    {
        return pathinfo($this->filePath, PATHINFO_EXTENSION);
    }

    /**
     * Returns the short hand git status of the file.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->fileStatus;
    }

    /**
     * Returns the git status of the file as a word.
     *
     * @return string
     *
     * @throws UnexpectedValueException
     */
    public function getFormattedStatus()
    {
        switch ($this->fileStatus) {
            case 'A':
                return 'added';
            case 'C':
                return 'copied';
            case 'M':
                return 'modified';
            case 'R':
                return 'renamed';
            default:
                throw new \UnexpectedValueException("Unknown file status: $this->fileStatus.");
        }
    }

    /**
     * Get the mime type for the file.
     *
     * @param  FileInterface $file
     * @return string
     */
    public function getMimeType()
    {
        // return mime type ala mimetype extension
        $finfo = finfo_open(FILEINFO_MIME);

        $mime = finfo_file($finfo, $this->getFullPath());

        return $mime;
    }
}
