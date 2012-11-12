<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Reflection\DocBlock\Tag;

/**
 * @category   Zend
 * @package    Zend_Reflection
 */
class ReturnTag implements TagInterface
{
    /**
     * @var string
     */
    protected $type = null;

    /**
     * @var string
     */
    protected $description = null;

    /**
     * @return string
     */
    public function getName()
    {
        return 'return';
    }

    /**
     * @param  string $tagDocBlockLine
     * @return void
     */
    public function initialize($tagDocBlockLine)
    {
        $matches = array();
        preg_match('#([\w|\\\]+)(?:\s+(.*))?#', $tagDocBlockLine, $matches);

        $this->type = $matches[1];

        if (isset($matches[2])) {
            $this->description = $matches[2];
        }
    }

    /**
     * Get return variable type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
