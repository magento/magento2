<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Exception;

use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Phrase;

/**
 * Exception for GraphQL to be thrown when data already exists
 */
class GraphQlAlreadyExistsException extends AlreadyExistsException implements ClientAware, ProvidesExtensions
{
    /**
     * Describing a category of the error
     */
    public const EXCEPTION_CATEGORY = 'graphql-already-exists';

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

    /**
     * Get error category
     *
     * @return array
     */
    public function getExtensions(): array
    {
        $exceptionCategory['category'] = $this->getCategory();
        return $exceptionCategory;
    }
}
