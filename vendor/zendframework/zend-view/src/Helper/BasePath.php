<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

use Zend\View\Exception;

/**
 * Helper for retrieving the base path.
 */
class BasePath extends AbstractHelper
{
    /**
     * Base path
     *
     * @var string
     */
    protected $basePath;

    /**
     * Returns site's base path, or file with base path prepended.
     *
     * $file is appended to the base path for simplicity.
     *
     * @param  string|null $file
     * @throws Exception\RuntimeException
     * @return string
     */
    public function __invoke($file = null)
    {
        if (null === $this->basePath) {
            throw new Exception\RuntimeException('No base path provided');
        }

        if (null !== $file) {
            $file = '/' . ltrim($file, '/');
        }

        return $this->basePath . $file;
    }

    /**
     * Set the base path.
     *
     * @param  string $basePath
     * @return self
     */
    public function setBasePath($basePath)
    {
        $this->basePath = rtrim($basePath, '/');
        return $this;
    }
}
