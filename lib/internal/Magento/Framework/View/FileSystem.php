<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

/**
 * Model that finds file paths by their fileId
 *
 * @api
 * @since 100.0.2
 */
class FileSystem
{
    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\File
     */
    protected $_fileResolution;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\TemplateFile
     */
    protected $_templateFileResolution;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\LocaleFile
     */
    protected $_localeFileResolution;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile
     */
    protected $_staticFileResolution;

    /**
     * @var \Magento\Framework\View\Design\FileResolution\Fallback\EmailTemplateFile
     */
    protected $_emailTemplateFileResolution;

    /**
     * View service
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Design\FileResolution\Fallback\File $fallbackFile
     * @param \Magento\Framework\View\Design\FileResolution\Fallback\TemplateFile $fallbackTemplateFile
     * @param \Magento\Framework\View\Design\FileResolution\Fallback\LocaleFile $fallbackLocaleFile
     * @param \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile $fallbackStaticFile
     * @param \Magento\Framework\View\Design\FileResolution\Fallback\EmailTemplateFile $fallbackEmailTemplateFile
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     */
    public function __construct(
        \Magento\Framework\View\Design\FileResolution\Fallback\File $fallbackFile,
        \Magento\Framework\View\Design\FileResolution\Fallback\TemplateFile $fallbackTemplateFile,
        \Magento\Framework\View\Design\FileResolution\Fallback\LocaleFile $fallbackLocaleFile,
        \Magento\Framework\View\Design\FileResolution\Fallback\StaticFile $fallbackStaticFile,
        \Magento\Framework\View\Design\FileResolution\Fallback\EmailTemplateFile $fallbackEmailTemplateFile,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->_fileResolution = $fallbackFile;
        $this->_templateFileResolution = $fallbackTemplateFile;
        $this->_localeFileResolution = $fallbackLocaleFile;
        $this->_staticFileResolution = $fallbackStaticFile;
        $this->_emailTemplateFileResolution = $fallbackEmailTemplateFile;
        $this->_assetRepo = $assetRepo;
    }

    /**
     * Get existing file name with fallback to default
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getFilename($fileId, array $params = [])
    {
        list($module, $filePath) = \Magento\Framework\View\Asset\Repository::extractModule(
            $this->normalizePath($fileId)
        );
        if ($module) {
            $params['module'] = $module;
        }
        $this->_assetRepo->updateDesignParams($params);
        $file = $this->_fileResolution
            ->getFile($params['area'], $params['themeModel'], $filePath, $params['module']);
        return $file;
    }

    /**
     * Get a locale file
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getLocaleFileName($file, array $params = [])
    {
        $this->_assetRepo->updateDesignParams($params);
        return $this->_localeFileResolution
            ->getFile($params['area'], $params['themeModel'], $params['locale'], $file);
    }

    /**
     * Get a template file
     *
     * @param string $fileId
     * @param array $params
     * @return string|bool
     */
    public function getTemplateFileName($fileId, array $params = [])
    {
        list($module, $filePath) = \Magento\Framework\View\Asset\Repository::extractModule(
            $this->normalizePath($fileId)
        );
        if ($module) {
            $params['module'] = $module;
        }
        $this->_assetRepo->updateDesignParams($params);
        return $this->_templateFileResolution
            ->getFile($params['area'], $params['themeModel'], $filePath, $params['module']);
    }

    /**
     * Find a static view file using fallback mechanism
     *
     * @param string $fileId
     * @param array $params
     * @return string
     */
    public function getStaticFileName($fileId, array $params = [])
    {
        list($module, $filePath) = \Magento\Framework\View\Asset\Repository::extractModule(
            $this->normalizePath($fileId)
        );
        if ($module) {
            $params['module'] = $module;
        }
        $this->_assetRepo->updateDesignParams($params);
        return $this->_staticFileResolution
            ->getFile($params['area'], $params['themeModel'], $params['locale'], $filePath, $params['module']);
    }

    /**
     * Get an email template file
     *
     * @param string $fileId
     * @param array $params
     * @param string $module
     * @return string|bool
     */
    public function getEmailTemplateFileName($fileId, array $params, $module)
    {
        $this->_assetRepo->updateDesignParams($params);
        return $this->_emailTemplateFileResolution
            ->getFile($params['area'], $params['themeModel'], $params['locale'], $fileId, $module);
    }

    /**
     * Remove excessive "." and ".." parts from a path
     *
     * For example foo/bar/../file.ext -> foo/file.ext
     *
     * @param string $path
     * @return string
     */
    public static function normalizePath($path)
    {
        $parts = explode('/', $path);
        $result = [];

        foreach ($parts as $part) {
            if ('..' === $part) {
                if (!count($result) || ($result[count($result) - 1] == '..')) {
                    $result[] = $part;
                } else {
                    array_pop($result);
                }
            } elseif ('.' !== $part) {
                $result[] = $part;
            }
        }
        return implode('/', $result);
    }

    /**
     * Get a relative path between $relatedPath and $path paths as if $path was to refer to $relatedPath
     * relatively of itself
     *
     * Returns new calculated relative path.
     * Examples:
     *   $path: /some/directory/one/file.ext
     *   $relatedPath: /some/directory/two/another/file.ext
     *   Result: ../two/another
     *
     *   $path: http://example.com/themes/demo/css/styles.css
     *   $relatedPath: http://example.com/images/logo.gif
     *   Result: ../../../images
     *
     * @param string $relatedPath
     * @param string $path
     * @return string
     */
    public static function offsetPath($relatedPath, $path)
    {
        $relatedPath = self::normalizePath($relatedPath);
        $path = self::normalizePath($path);
        list($relatedPath, $path) = self::ltrimSamePart($relatedPath, $path);
        $toDir = ltrim(dirname($path), '/');
        if ($toDir == '.') {
            $offset = '';
        } else {
            $offset = str_repeat('../', count(explode('/', $toDir)));
        }
        return rtrim($offset . dirname($relatedPath), '/');
    }

    /**
     * Concatenate/normalize a path to another path as a relative, assuming it will be relative to its directory
     *
     * @param string $relativeTo
     * @param string $path
     * @return string
     */
    public static function getRelatedPath($relativeTo, $path)
    {
        return self::normalizePath(dirname($relativeTo) . '/' . $path);
    }

    /**
     * Left-trim same part of two paths
     *
     * @param string $pathOne
     * @param string $pathTwo
     * @return array
     */
    private static function ltrimSamePart($pathOne, $pathTwo)
    {
        $one = explode('/', $pathOne);
        $two = explode('/', $pathTwo);
        while (isset($one[0]) && isset($two[0]) && $one[0] == $two[0]) {
            array_shift($one);
            array_shift($two);
        }
        return [implode('/', $one), implode('/', $two)];
    }
}
