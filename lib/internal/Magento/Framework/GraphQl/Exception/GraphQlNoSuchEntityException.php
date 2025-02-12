<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Exception;

use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;

/**
 * Exception for GraphQL to be thrown when entity does not exists
 *
 * @api
 */
class GraphQlNoSuchEntityException extends NoSuchEntityException implements ClientAware, ProvidesExtensions
{
    public const EXCEPTION_CATEGORY = 'graphql-no-such-entity';

    /**
     * @var boolean
     */
    private $isSafe;

    /**
     * Initialize object
     *
     * @param Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     * @param boolean $isSafe
     */
    public function __construct(Phrase $phrase, ?\Exception $cause = null, $code = 0, $isSafe = true)
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
