<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Exception;

use Magento\Framework\Phrase;

/**
 * Exception thrown while processing bulk of entities
 *
 * @api
 */
class BulkException extends AbstractAggregateException
{
    /**
     * @var array
     */
    private $data;

    /**
     * Exception thrown while processing bulk of entities
     *
     * It is capable of holding both successfully processed entities and failed entities.
     * Client can decide how to handle the information
     *
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase('One or more input exceptions have occurred while processing bulk.');
        }
        parent::__construct($phrase, $cause, $code);
    }

    /**
<<<<<<< HEAD
     * @param $data array
=======
     * Add data
     *
     * @param array $data
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function addData($data)
    {
        $this->data = $data;
    }

    /**
<<<<<<< HEAD
=======
     * Retrieve data
     *
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
