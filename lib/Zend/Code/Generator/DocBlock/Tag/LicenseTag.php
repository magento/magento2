<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Code
 */

namespace Zend\Code\Generator\DocBlock\Tag;

use Zend\Code\Generator\DocBlock\Tag;
use Zend\Code\Reflection\DocBlock\Tag\TagInterface as ReflectionDocBlockTag;

/**
 * @category   Zend
 * @package    Zend_Code_Generator
 */
class LicenseTag extends Tag
{

    /**
     * @var string
     */
    protected $url = null;

    /**
     * fromReflection()
     *
     * @param ReflectionDocBlockTag $reflectionTagLicense
     * @return LicenseTag
     */
    public static function fromReflection(ReflectionDocBlockTag $reflectionTagLicense)
    {
        $returnTag = new self();

        $returnTag->setName('license');
        $returnTag->setUrl($reflectionTagLicense->getUrl());
        $returnTag->setDescription($reflectionTagLicense->getDescription());

        return $returnTag;
    }

    /**
     * setUrl()
     *
     * @param string $url
     * @return LicenseTag
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * getUrl()
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * generate()
     *
     * @return string
     */
    public function generate()
    {
        return $output;
    }

}
