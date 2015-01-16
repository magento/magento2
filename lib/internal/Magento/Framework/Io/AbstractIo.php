<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Io;

/**
 * Install and upgrade client abstract class
 */
abstract class AbstractIo implements IoInterface
{
    /**
     * If this variable is set to true, our library will be able to automaticaly
     * create non-existant directories
     *
     * @var bool
     */
    protected $_allowCreateFolders = false;

    /**
     * Allow automaticaly create non-existant directories
     *
     * @param bool $flag
     * @return $this
     */
    public function setAllowCreateFolders($flag)
    {
        $this->_allowCreateFolders = (bool)$flag;
        return $this;
    }

    /**
     * Open a connection
     *
     * @param array $args
     * @return false
     */
    public function open(array $args = [])
    {
        return false;
    }

    /**
     * @return string
     */
    public function dirsep()
    {
        return '/';
    }

    /**
     * @param string $path
     * @return string
     */
    public function getCleanPath($path)
    {
        if (empty($path)) {
            return './';
        }

        $path = trim(preg_replace("/\\\\/", "/", (string)$path));

        if (!preg_match("/(\.\w{1,4})$/", $path) && !preg_match("/\?[^\\/]+$/", $path) && !preg_match("/\\/$/", $path)
        ) {
            $path .= '/';
        }

        $matches = [];
        $pattern = "/^(\\/|\w:\\/|https?:\\/\\/[^\\/]+\\/)?(.*)$/i";
        preg_match_all($pattern, $path, $matches, PREG_SET_ORDER);

        $pathTokR = $matches[0][1];
        $pathTokP = $matches[0][2];

        $pathTokP = preg_replace(["/^\\/+/", "/\\/+/"], ["", "/"], $pathTokP);

        $pathParts = explode("/", $pathTokP);
        $realPathParts = [];

        for ($i = 0, $realPathParts = []; $i < count($pathParts); $i++) {
            if ($pathParts[$i] == '.') {
                continue;
            } elseif ($pathParts[$i] == '..') {
                if (isset($realPathParts[0]) && $realPathParts[0] != '..' || $pathTokR != "") {
                    array_pop($realPathParts);
                    continue;
                }
            }

            $realPathParts[] = $pathParts[$i];
        }

        return $pathTokR . implode('/', $realPathParts);
    }

    /**
     * @param string $haystackPath
     * @param string $needlePath
     * @return bool
     */
    public function allowedPath($haystackPath, $needlePath)
    {
        return strpos($this->getCleanPath($haystackPath), $this->getCleanPath($needlePath)) === 0;
    }
}
