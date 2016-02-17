<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form;

interface ElementAttributeRemovalInterface
{
    /**
     * Remove a single element attribute
     *
     * @param  string $key
     * @return ElementAttributeRemovalInterface
     */
    public function removeAttribute($key);

    /**
     * Remove many attributes at once
     *
     * @param array $keys
     * @return ElementAttributeRemovalInterface
     */
    public function removeAttributes(array $keys);

    /**
     * Remove all attributes at once
     *
     * @return ElementAttributeRemovalInterface
     */
    public function clearAttributes();
}
