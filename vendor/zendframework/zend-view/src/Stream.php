<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View;

/**
 * Stream wrapper to convert markup of mostly-PHP templates into PHP prior to
 * include().
 *
 * Based in large part on the example at
 * http://www.php.net/manual/en/function.stream-wrapper-register.php
 *
 * As well as the example provided at:
 *     http://mikenaberezny.com/2006/02/19/symphony-templates-ruby-erb/
 * written by
 *     Mike Naberezny (@link http://mikenaberezny.com)
 *     Paul M. Jones  (@link http://paul-m-jones.com)
 */
class Stream
{
    /**
     * Current stream position.
     *
     * @var int
     */
    protected $pos = 0;

    /**
     * Data for streaming.
     *
     * @var string
     */
    protected $data;

    /**
     * Stream stats.
     *
     * @var array
     */
    protected $stat;

    /**
     * Opens the script file and converts markup.
     *
     * @param  string $path
     * @param         $mode
     * @param         $options
     * @param         $opened_path
     * @return bool
     */
    public function stream_open($path, $mode, $options, &$opened_path)
    {
        // get the view script source
        $path        = str_replace('zend.view://', '', $path);
        $this->data = file_get_contents($path);

        /**
         * If reading the file failed, update our local stat store
         * to reflect the real stat of the file, then return on failure
         */
        if ($this->data === false) {
            $this->stat = stat($path);
            return false;
        }

        /**
         * Convert <?= ?> to long-form <?php echo ?> and <?php ?> to <?php ?>
         *
         */
        $this->data = preg_replace('/\<\?\=/', "<?php echo ", $this->data);
        $this->data = preg_replace('/<\?(?!xml|php)/s', '<?php ', $this->data);

        /**
         * file_get_contents() won't update PHP's stat cache, so we grab a stat
         * of the file to prevent additional reads should the script be
         * requested again, which will make include() happy.
         */
        $this->stat = stat($path);

        return true;
    }

    /**
     * Included so that __FILE__ returns the appropriate info
     *
     * @return array
     */
    public function url_stat()
    {
        return $this->stat;
    }

    /**
     * Reads from the stream.
     *
     * @param  int $count
     * @return string
     */
    public function stream_read($count)
    {
        $ret = substr($this->data, $this->pos, $count);
        $this->pos += strlen($ret);
        return $ret;
    }

    /**
     * Tells the current position in the stream.
     *
     * @return int
     */
    public function stream_tell()
    {
        return $this->pos;
    }

    /**
     * Tells if we are at the end of the stream.
     *
     * @return bool
     */
    public function stream_eof()
    {
        return $this->pos >= strlen($this->data);
    }

    /**
     * Stream statistics.
     *
     * @return array
     */
    public function stream_stat()
    {
        return $this->stat;
    }

    /**
     * Seek to a specific point in the stream.
     *
     * @param  $offset
     * @param  $whence
     * @return bool
     */
    public function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->data) && $offset >= 0) {
                    $this->pos = $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            case SEEK_CUR:
                if ($offset >= 0) {
                    $this->pos += $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            case SEEK_END:
                if (strlen($this->data) + $offset >= 0) {
                    $this->pos = strlen($this->data) + $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            default:
                return false;
        }
    }
}
