<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

class EncodedContext
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $initializationVector;

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
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
     * @param $initializationVector
     * @return $this
     */
    public function setInitializationVector($initializationVector)
    {
        $this->initializationVector = $initializationVector;
        return $this;
    }
}
