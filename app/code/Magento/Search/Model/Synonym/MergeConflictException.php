<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\Synonym;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Exception class for merge conflict during inserting and updating synonym groups
 *
 * @api
 * @since 2.1.0
 */
class MergeConflictException extends LocalizedException
{
    /**
     * Conflicting synonyms
     *
     * @var array
     * @since 2.1.0
     */
    private $conflictingSynonyms;

    /**
     * Constructor
     *
     * @param array $conflictingSynonyms
     * @param Phrase|null $phrase
     * @param \Exception|null $cause
     * @param int $code
     * @since 2.1.0
     */
    public function __construct(array $conflictingSynonyms, Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        parent::__construct($phrase, $cause, $code);
        $this->conflictingSynonyms = $conflictingSynonyms;
    }

    /**
     * Gets conflicting synonyms
     *
     * @return array
     * @since 2.1.0
     */
    public function getConflictingSynonyms()
    {
        return $this->conflictingSynonyms;
    }
}
