<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Generator;

/**
 * @category   Zend
 * @package    Zend_Code_Generator
 */
class BodyGenerator extends AbstractGenerator
{

    /**
     * @var string
     */
    protected $content = null;

    /**
     * setContent()
     *
     * @param string $content
     * @return BodyGenerator
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * getContent()
     *
     * @return string
     */
    public function getContent()
    {
        return (string) $this->content;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        return $this->getContent();
    }
}
