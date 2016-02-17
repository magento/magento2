<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter\Word;

class SeparatorToDash extends SeparatorToSeparator
{
    /**
     * Constructor
     *
     * @param string $searchSeparator Separator to search for change
     */
    public function __construct($searchSeparator = ' ')
    {
        parent::__construct($searchSeparator, '-');
    }
}
