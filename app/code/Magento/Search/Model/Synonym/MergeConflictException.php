<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Search\Model\Synonym;

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
     * Conflicting synonyms
     *
     * @var array
     */
    private $conflictingSynonyms;

    /**
     * Constructor
     *
     * @param array $conflictingSynonyms
     * @param Phrase|null $phrase
     * @param \Exception|null $cause
     * @param int $code
     */
    public function __construct(array $conflictingSynonyms, ?Phrase $phrase = null, ?\Exception $cause = null, $code = 0)
    {
        parent::__construct($phrase, $cause, $code);
        $this->conflictingSynonyms = $conflictingSynonyms;
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
