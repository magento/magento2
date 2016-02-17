<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Serializer\Adapter;

class WddxOptions extends AdapterOptions
{
    /**
     * Wddx packet header comment
     *
     * @var string
     */
    protected $comment = '';

    /**
     * Set WDDX header comment
     *
     * @param  string $comment
     * @return WddxOptions
     */
    public function setComment($comment)
    {
        $this->comment = (string) $comment;
        return $this;
    }

    /**
     * Get WDDX header comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }
}
