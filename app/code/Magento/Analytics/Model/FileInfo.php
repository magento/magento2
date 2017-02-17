<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

class FileInfo
{
    /**
     * @var string
     */
    private $initializationVector;

    /**
     * @var string
     */
    private $path;

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getInitializationVector()
    {
        return $this->initializationVector;
    }

    /**
     * @param string $initializationVector
     * @return $this
     */
    public function setInitializationVector($initializationVector)
    {
        $this->initializationVector = $initializationVector;
        return $this;
    }
}
