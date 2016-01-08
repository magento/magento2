<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\Synonym;

class MergeConflictException extends \Exception
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
     * @param string $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct(array $conflictingSynonyms, $message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
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
