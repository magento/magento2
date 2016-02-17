<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Code\Reflection\DocBlock\Tag;

class LicenseTag implements TagInterface
{
    /**
     * @var string
     */
    protected $url = null;

    /**
     * @var string
     */
    protected $licenseName = null;

    /**
     * @return string
     */
    public function getName()
    {
        return 'license';
    }

    /**
     * Initializer
     *
     * @param  string $tagDocblockLine
     */
    public function initialize($tagDocblockLine)
    {
        $match = array();

        if (!preg_match('#^([\S]*)(?:\s+(.*))?$#m', $tagDocblockLine, $match)) {
            return;
        }

        if ($match[1] !== '') {
            $this->url = trim($match[1]);
        }

        if (isset($match[2]) && $match[2] !== '') {
            $this->licenseName = $match[2];
        }
    }

    /**
     * @return null|string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return null|string
     */
    public function getLicenseName()
    {
        return $this->licenseName;
    }

    public function __toString()
    {
        return 'DocBlock Tag [ * @' . $this->getName() . ' ]' . PHP_EOL;
    }
}
