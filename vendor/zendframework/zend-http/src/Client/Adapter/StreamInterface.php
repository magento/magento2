<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Http\Client\Adapter;

/**
 * An interface description for Zend\Http\Client\Adapter\Stream classes.
 *
 * This interface describes Zend\Http\Client\Adapter which supports streaming.
 */
interface StreamInterface
{
    /**
     * Set output stream
     *
     * This function sets output stream where the result will be stored.
     *
     * @param resource $stream Stream to write the output to
     *
     */
    public function setOutputStream($stream);
}
