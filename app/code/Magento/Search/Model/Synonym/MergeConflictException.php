<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\Synonym;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Exception class for merge conflict during inserting and updating synonym groups
 *
 * @api
 * @since 100.1.0
 */
class MergeConflictException extends LocalizedException
{
    /**
     * Constructor
     *
     * @param array $conflictingSynonyms Conflicting synonyms
     * @param Phrase|null $phrase
     * @param Exception|null $cause
     * @param int $code
     */
    public function __construct(
        private readonly array $conflictingSynonyms,
        Phrase $phrase = null,
        Exception $cause = null,
        $code = 0
    ) {
        parent::__construct($phrase, $cause, $code);
    }

    /**
     * Gets conflicting synonyms
     *
     * @return array
     * @since 100.1.0
     */
    public function getConflictingSynonyms()
    {
        return $this->conflictingSynonyms;
    }
}
