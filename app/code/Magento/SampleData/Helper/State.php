<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Helper;

class State
{
    const STATE_NOT_STARTED = 'not_started';

    const STATE_STARTED = 'started';

    const STATE_FINISHED = 'finished';

    const ERROR = 'error';

    /**
     * @var array
     */
    protected $allowedStates = [
        self::STATE_STARTED,
        self::STATE_FINISHED
    ];

    /**
     * @var string
     */
    protected $fileName = 'sample-data-state.flag';

    /**
     * @var string
     */
    protected $errorFileName = 'sample-data-error.flag';

    /**
     * Get file resource to write sample data installation state
     *
     * @param string $mode
     * @param string $fileName
     * @return resource|false
     */
    protected function getStream($mode = 'r', $fileName)
    {
        $filePath = BP . '/var/' . $fileName;
        $stream = @fopen($filePath, $mode);
        return $stream;
    }

    /**
     * Closing file stream
     *
     * @param resource|false $handle
     * @return void
     */
    protected function closeStream($handle)
    {
        if ($handle) {
            fclose($handle);
        }
    }

    /**
     * Verify if correct state provided
     *
     * @param string $state
     * @return bool
     */
    protected function isStateCorrect($state)
    {
        return in_array($state, $this->allowedStates);
    }

    /**
     * State getter
     *
     * @return string
     */
    public function getState()
    {
        $defaultState = self::STATE_NOT_STARTED;
        $stream = $this->getStream('r', $this->fileName);
        if (!$stream) {
            return $defaultState;
        }
        $state = trim(fread($stream, 400));
        $this->closeStream($stream);
        if ($this->isStateCorrect($state)) {
            return $state;
        }
        return $defaultState;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        $isError = false;
        $stream = $this->getStream('r', $this->errorFileName);
        if (!$stream) {
            return $isError;
        } elseif (trim(fread($stream, 400)) == self::ERROR) {
            $isError = true;
        }
        $this->closeStream($stream);
        return $isError;
    }

    /**
     * @param string $state
     * @return $this
     * @throws \Exception
     */
    protected function setState($state)
    {
        if ($this->isStateCorrect($state)) {
            $this->writeStream($state, $this->fileName);
        }
        return $this;
    }

    /**
     * @param string $data
     * @param string $fileName
     * @throws \Exception
     */
    protected function writeStream($data, $fileName)
    {
        $stream = $this->getStream('w', $fileName);
        if ($stream === false) {
            throw new \Exception(
                'Please, ensure that file var/' . $fileName . ' inside Sample data directory exists and is writable'
            );
        }
        fwrite($stream, $data);
        $this->closeStream($stream);
    }

    /**
     * @return $this
     */
    public function start()
    {
        return $this->setState(self::STATE_STARTED);
    }

    /**
     * @return $this
     */
    public function finish()
    {
        return $this->setState(self::STATE_FINISHED);
    }

    /**
     * @return State
     */
    public function setError()
    {
        return $this->writeStream(self::ERROR, $this->errorFileName);
    }

    public function clearErrorFlag()
    {
        $this->writeStream('', $this->errorFileName);
    }

    /**
     * @return bool
     */
    public function getError()
    {
        return $this->isError();
    }
}
