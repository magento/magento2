<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Exception;

use GraphQL\Error\ClientAware;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Phrase;

/**
 * Class GraphQlAlreadyExistsException
 */
class GraphQlAlreadyExistsException extends AlreadyExistsException implements ClientAware
{
    /**
     * Describing a category of the error
     */
    const EXCEPTION_CATEGORY = 'graphql-already-exists';

    /**
     * @var boolean
     */
    private $isSafe;

    /**
     * @param Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     * @param boolean $isSafe
     */
    public function __construct(Phrase $phrase, \Exception $cause = null, $code = 0, $isSafe = true)
    {
        $this->isSafe = $isSafe;
        parent::__construct($phrase, $cause, $code);
    }

    /**
     * @inheritdoc
     */
    public function isClientSafe(): bool
    {
        return $this->isSafe;
    }

    /**
     * @inheritdoc
     */
    public function getCategory(): string
    {
        return self::EXCEPTION_CATEGORY;
    }
}
