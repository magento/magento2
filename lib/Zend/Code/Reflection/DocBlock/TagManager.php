<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Reflection\DocBlock;

use Zend\Code\Reflection\Exception;

class TagManager
{
    const USE_DEFAULT_PROTOTYPES = 'default';

    protected $tagNames = array();
    protected $tags = array();
    protected $genericTag = null;

    public function __construct($prototypes = null)
    {
        if (is_array($prototypes)) {
            foreach ($prototypes as $prototype) {
                $this->addTagPrototype($prototype);
            }
        } elseif ($prototypes === self::USE_DEFAULT_PROTOTYPES) {
            $this->useDefaultPrototypes();
        }
    }

    public function useDefaultPrototypes()
    {
        $this->addTagPrototype(new Tag\ParamTag());
        $this->addTagPrototype(new Tag\ReturnTag());
        $this->addTagPrototype(new Tag\MethodTag());
        $this->addTagPrototype(new Tag\PropertyTag());
        $this->addTagPrototype(new Tag\GenericTag());
    }

    public function addTagPrototype(Tag\TagInterface $tag)
    {
        $tagName = str_replace(array('-', '_'), '', $tag->getName());

        if (in_array($tagName, $this->tagNames)) {
            throw new Exception\InvalidArgumentException('A tag with this name already exists in this manager');
        }

        $this->tagNames[] = $tagName;
        $this->tags[]     = $tag;

        if ($tag instanceof Tag\GenericTag) {
            $this->genericTag = $tag;
        }
    }

    public function hasTag($tagName)
    {
        // otherwise, only if its name exists as a key
        return in_array(str_replace(array('-', '_'), '', $tagName), $this->tagNames);
    }

    public function createTag($tagName, $content = null)
    {
        $tagName = str_replace(array('-', '_'), '', $tagName);

        if (!$this->hasTag($tagName) && !isset($this->genericTag)) {
            throw new Exception\RuntimeException('This tag name is not supported by this tag manager');
        }

        $index = array_search($tagName, $this->tagNames);

        /* @var Tag\TagInterface $tag */
        $tag = ($index !== false) ? $this->tags[$index] : $this->genericTag;

        $newTag = clone $tag;
        if ($content) {
            $newTag->initialize($content);
        }

        if ($newTag instanceof Tag\GenericTag) {
            $newTag->setName($tagName);
        }

        return $newTag;
    }

}
