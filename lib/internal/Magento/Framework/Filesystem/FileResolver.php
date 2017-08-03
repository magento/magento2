<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filesystem;

/**
 * Contains logic for finding class filepaths based on include_path configuration.
 * @since 2.0.0
 */
class FileResolver
{
    /**
     * Find a file in include path. Include path is set in composer.json or with set_include_path()
     *
     * @param string $class
     * @return string|bool
     * @since 2.0.0
     */
    public function getFile($class)
    {
        $relativePath = $this->getFilePath($class);
        return stream_resolve_include_path($relativePath);
    }

    /**
     * Get relative file path for specified class
     *
     * @param string $class
     * @return string
     * @since 2.0.0
     */
    public function getFilePath($class)
    {
        return ltrim(str_replace(['_', '\\'], '/', $class), '/') . '.php';
    }

    /**
     * Add specified path(s) to the current include_path
     *
     * @param string|array $path
     * @param bool         $prepend Whether to prepend paths or to append them
     * @return void
     * @since 2.0.0
     */
    public static function addIncludePath($path, $prepend = true)
    {
        $includePathExtra = implode(PATH_SEPARATOR, (array)$path);
        $includePath = get_include_path();
        $pathSeparator = $includePath && $includePathExtra ? PATH_SEPARATOR : '';
        if ($prepend) {
            $includePath = $includePathExtra . $pathSeparator . $includePath;
        } else {
            $includePath = $includePath . $pathSeparator . $includePathExtra;
        }
        set_include_path($includePath);
    }
}
