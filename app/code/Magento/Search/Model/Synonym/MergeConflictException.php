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
     */
    public function __construct(array $conflictingSynonyms, Phrase $phrase = null, \Exception $cause = null)
    {
        parent::__construct($phrase, $cause);
        $this->conflictingSynonyms = $conflictingSynonyms;
    }

    /**
     * Gets conflicting synonyms
     *
     * @return array
     */
    public function getConflictingSynonyms()
    {
        return $this->conflictingSynonyms;
    }
}
