<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Code\Exception;

class ClassReaderException extends \ReflectionException
{
    /** @var string */
    private $className;

    /**
     * @param $className
     * @return ClassReaderException
     */
    public function setClassName(string $className) : ClassReaderException
    {
       $this->className = $className;

       return $this;
    }

    /**
     * @return string
     */
    public function getClassName() : string
    {
        return $this->className;
    }
}