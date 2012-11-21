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

use Zend\Code\Generator;
use Zend\Code\Generator\Exception;

/**
 * @category   Zend
 * @package    Zend_Code_Generator
 */
abstract class AbstractMemberGenerator extends AbstractGenerator
{

    /**#@+
     * @const int Flags for construction usage
     */
    const FLAG_ABSTRACT  = 0x01;
    const FLAG_FINAL     = 0x02;
    const FLAG_STATIC    = 0x04;
    const FLAG_PUBLIC    = 0x10;
    const FLAG_PROTECTED = 0x20;
    const FLAG_PRIVATE   = 0x40;
    /**#@-*/

    /**#@+
     * @param const string
     */
    const VISIBILITY_PUBLIC    = 'public';
    const VISIBILITY_PROTECTED = 'protected';
    const VISIBILITY_PRIVATE   = 'private';
    /**#@-*/

    /**
     * @var \Zend\Code\Generator\DocBlockGenerator
     */
    protected $docBlock = null;

    /**
     * @var string
     */
    protected $name = null;

    /**
     * @var int
     */
    protected $flags = self::FLAG_PUBLIC;

    /**
     * Set flags
     *
     * @param int|array $flags
     * @return AbstractMemberGenerator
     */
    public function setFlags($flags)
    {

        if (is_array($flags)) {
            $flagsArray = $flags;
            $flags      = 0x00;
            foreach ($flagsArray as $flag) {
                $flags |= $flag;
            }
        }
        // check that visibility is one of three
        $this->flags = $flags;
        return $this;
    }

    /**
     * Add flag
     *
     * @param int $flag
     * @return AbstractMemberGenerator
     */
    public function addFlag($flag)
    {
        $this->setFlags($this->flags | $flag);
        return $this;
    }

    /**
     * Remove flag
     *
     * @param int $flag
     * @return AbstractMemberGenerator
     */
    public function removeFlag($flag)
    {
        $this->setFlags($this->flags & ~$flag);
        return $this;
    }


    /**
     * Set the DocBlock
     *
     * @param DocBlockGenerator|string $docBlock
     * @throws Exception\InvalidArgumentException
     * @return AbstractMemberGenerator
     */
    public function setDocBlock($docBlock)
    {
        if (is_string($docBlock)) {
            $docBlock = new DocBlockGenerator($docBlock);
        } elseif (!$docBlock instanceof DocBlockGenerator) {
            throw new Exception\InvalidArgumentException(
                'setDocBlock() is expecting either a string, array or an instance of Zend\Code\Generator\DocBlockGenerator'
            );
        }

        $this->docBlock = $docBlock;
        return $this;
    }

    /**
     * getDocBlock()
     *
     * @return DocBlockGenerator
     */
    public function getDocBlock()
    {
        return $this->docBlock;
    }

    /**
     * setAbstract()
     *
     * @param bool $isAbstract
     * @return AbstractMemberGenerator
     */
    public function setAbstract($isAbstract)
    {
        return (($isAbstract) ? $this->addFlag(self::FLAG_ABSTRACT) : $this->removeFlag(self::FLAG_ABSTRACT));
    }

    /**
     * isAbstract()
     *
     * @return bool
     */
    public function isAbstract()
    {
        return ($this->flags & self::FLAG_ABSTRACT);
    }

    /**
     * setFinal()
     *
     * @param bool $isFinal
     * @return AbstractMemberGenerator
     */
    public function setFinal($isFinal)
    {
        return (($isFinal) ? $this->addFlag(self::FLAG_FINAL) : $this->removeFlag(self::FLAG_FINAL));
    }

    /**
     * isFinal()
     *
     * @return bool
     */
    public function isFinal()
    {
        return ($this->flags & self::FLAG_FINAL);
    }

    /**
     * setStatic()
     *
     * @param bool $isStatic
     * @return AbstractMemberGenerator
     */
    public function setStatic($isStatic)
    {
        return (($isStatic) ? $this->addFlag(self::FLAG_STATIC) : $this->removeFlag(self::FLAG_STATIC));
    }

    /**
     * isStatic()
     *
     * @return bool
     */
    public function isStatic()
    {
        return (bool) ($this->flags & self::FLAG_STATIC); // is FLAG_STATIC in flags
    }

    /**
     * setVisibility()
     *
     * @param string $visibility
     * @return AbstractMemberGenerator
     */
    public function setVisibility($visibility)
    {
        switch ($visibility) {
            case self::VISIBILITY_PUBLIC:
                $this->removeFlag(self::FLAG_PRIVATE | self::FLAG_PROTECTED); // remove both
                $this->addFlag(self::FLAG_PUBLIC);
                break;
            case self::VISIBILITY_PROTECTED:
                $this->removeFlag(self::FLAG_PUBLIC | self::FLAG_PRIVATE); // remove both
                $this->addFlag(self::FLAG_PROTECTED);
                break;
            case self::VISIBILITY_PRIVATE:
                $this->removeFlag(self::FLAG_PUBLIC | self::FLAG_PROTECTED); // remove both
                $this->addFlag(self::FLAG_PRIVATE);
                break;
        }
        return $this;
    }

    /**
     * getVisibility()
     *
     * @return string
     */
    public function getVisibility()
    {
        switch (true) {
            case ($this->flags & self::FLAG_PROTECTED):
                return self::VISIBILITY_PROTECTED;
            case ($this->flags & self::FLAG_PRIVATE):
                return self::VISIBILITY_PRIVATE;
            default:
                return self::VISIBILITY_PUBLIC;
        }
    }

    /**
     * setName()
     *
     * @param string $name
     * @return AbstractMemberGenerator
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

}
