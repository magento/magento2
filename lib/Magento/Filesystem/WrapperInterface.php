<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Filesystem;

interface WrapperInterface
{
    /**
     * @return mixed
     */
    public function dir_closedir();

    /**
     * @param $path
     * @param $options
     * @return mixed
     */
    public function dir_opendir($path, $options);

    /**
     * @return mixed
     */
    public function dir_readdir();

    /**
     * @return mixed
     */
    public function dir_rewinddir();

    /**
     * @param $path
     * @param $mode
     * @param $options
     * @return mixed
     */
    public function mkdir($path, $mode, $options);

    /**
     * @param $from
     * @param $to
     * @return mixed
     */
    public function rename($from, $to);

    /**
     * @param $path
     * @param $options
     * @return mixed
     */
    public function rmdir($path, $options);

    /**
     * @param $cast
     * @return mixed
     */
    public function stream_cast($cast);

    /**
     * @return mixed
     */
    public function stream_close();

    /**
     * @return mixed
     */
    public function stream_eof();

    /**
     * @return mixed
     */
    public function stream_flush();

    /**
     * @param $operation
     * @return mixed
     */
    public function stream_lock($operation);

    /**
     * @param $path
     * @param $option
     * @param $value
     * @return mixed
     */
    public function stream_metadata($path, $option, $value);

    /**
     * @param $path
     * @param $mode
     * @param $options
     * @param $openedPath
     * @return mixed
     */
    public function stream_open($path, $mode, $options, &$openedPath);

    /**
     * @param $count
     * @return mixed
     */
    public function stream_read($count);

    /**
     * @param $offset
     * @param int $whence
     * @return mixed
     */
    public function stream_seek($offset, $whence = SEEK_SET);

    /**
     * @param $option
     * @param $arg1
     * @param $arg2
     * @return mixed
     */
    public function stream_set_option($option, $arg1, $arg2);

    /**
     * @return mixed
     */
    public function stream_stat();

    /**
     * @return mixed
     */
    public function stream_tell();

    /**
     * @param $newSize
     * @return mixed
     */
    public function stream_truncate ($newSize);

    /**
     * @param $data
     * @return mixed
     */
    public function stream_write($data);

    /**
     * @param $path
     * @return mixed
     */
    public function unlink($path);

    /**
     * @param $path
     * @param $flags
     * @return mixed
     */
    public function url_stat($path, $flags);
} 
