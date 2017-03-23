<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Api;

/**
 * @api
 */
interface SynonymAnalyzerInterface
{
    /**
     * Get synonyms for specified phrase
     *
     * For phrase: "Elizabeth is the English queen" example output is an array of arrays containing synonyms for each
     * word in the phrase:
     *
     * [
     *   0 => [ 0 => "elizabeth" ],
     *   1 => [ 0 => "is" ],
     *   2 => [ 0 => "the" ],
     *   3 => [ 0 => "british", 1 => "english" ],
     *   4 => [ 0 => "queen", 1 => "monarch" ]
     * ]
     *
     * @param string $phrase
     * @return array
     */
    public function getSynonymsForPhrase($phrase);
}
