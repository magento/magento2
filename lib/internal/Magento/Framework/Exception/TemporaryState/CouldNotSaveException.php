<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Exception\TemporaryState;

use Magento\Framework\Exception\TemporaryStateExceptionInterface;
use Magento\Framework\Exception\CouldNotSaveException as LocalizedCouldNotSaveException;
use Magento\Framework\Phrase;

/**
 * CouldNotSaveException caused by recoverable error
 *
 * @api
 * @since 101.0.0
 */
class CouldNotSaveException extends LocalizedCouldNotSaveException implements TemporaryStateExceptionInterface
{
    /**
     * Class constructor
     *
     * @param Phrase $phrase The Exception message to throw.
     * @param \Exception $previous [optional] The previous exception used for the exception chaining.
     * @param int $code [optional] The Exception code.
     */
    public function __construct(Phrase $phrase, ?\Exception $previous = null, $code = 0)
    {
        parent::__construct($phrase, $previous, $code);
        $this->code = $code;
    }
}
