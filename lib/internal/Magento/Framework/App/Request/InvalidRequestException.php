<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Request;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Phrase;

/**
 * Received request is invalid.
 */
class InvalidRequestException extends RuntimeException
{
    /**
     * @var ResponseInterface|ResultInterface
     */
    private $replaceResult;

    /**
     * @var Phrase[]|null
     */
    private $messages;

    /**
     * @param ResponseInterface|ResultInterface $replaceResult Use this result
     * instead of calling action instance.
     * @param Phrase[]|null $messages Messages to show to client
     * as error messages.
     */
    public function __construct($replaceResult, ?array $messages = null)
    {
        parent::__construct(new Phrase('Invalid request received'));

        $this->replaceResult = $replaceResult;
        $this->messages = $messages;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function getReplaceResult()
    {
        return $this->replaceResult;
    }

    /**
     * @return Phrase[]|null
     */
    public function getMessages(): ?array
    {
        return $this->messages;
    }
}
