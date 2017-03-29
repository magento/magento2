<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleAsyncAmqp\Model;

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
    public function setTextFilePath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getTextFilePath()
    {
        return $this->path;
    }

    /**
     * @param string $strValue
     * @return void
     */
    public function setValue($strValue)
    {
        $this->msgValue = $strValue;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->msgValue;
    }
}
