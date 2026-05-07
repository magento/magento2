<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleAsyncStomp\Model;

class AsyncTestData
{
    /**
     * @var $msgValue
     */
    protected $msgValue;

    /**
     * @var $path
     */
    protected $path;

    /**
     * set path to tmp directory.
     *
     * @param string $path
     * @return void
     */
    public function setTextFilePath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getTextFilePath(): string
    {
        return $this->path;
    }

    /**
     * @param string $strValue
     * @return void
     */
    public function setValue($strValue): void
    {
        $this->msgValue = $strValue;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->msgValue;
    }
}
