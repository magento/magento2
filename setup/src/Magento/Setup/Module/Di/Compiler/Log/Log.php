<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\Compiler\Log;

class Log
{
    const GENERATION_ERROR = 1;

    const GENERATION_SUCCESS = 2;

    const COMPILATION_ERROR = 3;

    const CONFIGURATION_ERROR = 4;

    /**
     * Success log writer
     *
     * @var Writer\Console
     */
    protected $_successWriter;

    /**
     * Error log writer
     *
     * @var Writer\Console
     */
    protected $_errorWriter;

    /**
     * List of success log entries
     *
     * @var array
     */
    protected $_successEntries = [];

    /**
     * List of error entries
     *
     * @var array
     */
    protected $_errorEntries = [];

    /**
     * @param Writer\Console $successWriter
     * @param Writer\Console $errorWriter
     */
    public function __construct(Writer\Console $successWriter, Writer\Console $errorWriter)
    {
        $this->_successWriter = $successWriter;
        $this->_errorWriter = $errorWriter;
        $this->_successEntries[self::GENERATION_SUCCESS] = [];
        $this->_errorEntries = [
            self::CONFIGURATION_ERROR => [],
            self::GENERATION_ERROR => [],
            self::COMPILATION_ERROR => [],
        ];
    }

    /**
     * Add log message
     *
     * @param string $type
     * @param string $key
     * @param string $message
     * @return void
     */
    public function add($type, $key, $message = '')
    {
        if (array_key_exists($type, $this->_successEntries)) {
            $this->_successEntries[$type][$key][] = $message;
        } else {
            $this->_errorEntries[$type][$key][] = $message;
        }
    }

    /**
     * Write entries
     *
     * @return void
     * @throws \Magento\Framework\Validator\Exception
     */
    public function report()
    {
        $this->_successWriter->write($this->_successEntries);
        $this->_errorWriter->write($this->_errorEntries);
        if (count($this->_errorEntries) > 0) {
            throw new \Magento\Framework\Validator\Exception(__('Error during compilation'));
        }
    }

    /**
     * Check whether error exists
     *
     * @return bool
     */
    public function hasError()
    {
        foreach ($this->_errorEntries as $data) {
            if (count($data)) {
                return true;
            }
        }
        return false;
    }
}
