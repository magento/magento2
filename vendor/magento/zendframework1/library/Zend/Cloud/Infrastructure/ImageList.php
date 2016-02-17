<?php
/**
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage Infrastructure
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


#require_once 'Zend/Cloud/Infrastructure/Image.php';

/**
 * List of images
 *
 * @package    Zend_Cloud
 * @subpackage Infrastructure
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_Infrastructure_ImageList implements Countable, Iterator, ArrayAccess
{
    /**
     * @var array Array of Zend_Cloud_Infrastructure_Image
     */
    protected $images = array();

    /**
     * @var int Iterator key
     */
    protected $iteratorKey = 0;

    /**
     * The Image adapter (if exists)
     *
     * @var object
     */
    protected $adapter;

    /**
     * Constructor
     *
     * @param  array $list
     * @param  null|object $adapter
     * @return boolean
     */
    public function __construct($images, $adapter = null)
    {
        if (empty($images) || !is_array($images)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception(__CLASS__ . ' expects an array of images');
        }

        $this->adapter = $adapter;
        $this->constructFromArray($images);
    }

    /**
     * Transforms the Array to array of Instances
     *
     * @param  array $list
     * @return void
     */
    protected function constructFromArray(array $list)
    {
        foreach ($list as $image) {
            $this->addImage(new Zend_Cloud_Infrastructure_Image($image, $this->adapter));
        }
    }

    /**
     * Add an image
     *
     * @param  Image
     * @return ImageList
     */
    protected function addImage(Zend_Cloud_Infrastructure_Image $image)
    {
        $this->images[] = $image;
        return $this;
    }

    /**
     * Return number of images
     *
     * Implement Countable::count()
     *
     * @return int
     */
    public function count()
    {
        return count($this->images);
    }

    /**
     * Return the current element
     *
     * Implement Iterator::current()
     *
     * @return Image
     */
    public function current()
    {
        return $this->images[$this->iteratorKey];
    }

    /**
     * Return the key of the current element
     *
     * Implement Iterator::key()
     *
     * @return int
     */
    public function key()
    {
        return $this->iteratorKey;
    }

    /**
     * Move forward to next element
     *
     * Implement Iterator::next()
     *
     * @return void
     */
    public function next()
    {
        $this->iteratorKey++;
    }

    /**
     * Rewind the Iterator to the first element
     *
     * Implement Iterator::rewind()
     *
     * @return void
     */
    public function rewind()
    {
        $this->iteratorKey = 0;
    }

    /**
     * Check if there is a current element after calls to rewind() or next()
     *
     * Implement Iterator::valid()
     *
     * @return bool
     */
    public function valid()
    {
        $numItems = $this->count();
        if ($numItems > 0 && $this->iteratorKey < $numItems) {
            return true;
        }
        return false;
    }

    /**
     * Whether the offset exists
     *
     * Implement ArrayAccess::offsetExists()
     *
     * @param   int     $offset
     * @return  bool
     */
    public function offsetExists($offset)
    {
        return ($offset < $this->count());
    }

    /**
     * Return value at given offset
     *
     * Implement ArrayAccess::offsetGet()
     *
     * @param   int     $offset
     * @throws  Zend_Cloud_Infrastructure_Exception
     * @return  Image
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('Illegal index');
        }
        return $this->images[$offset];
    }

    /**
     * Throws exception because all values are read-only
     *
     * Implement ArrayAccess::offsetSet()
     *
     * @param   int     $offset
     * @param   string  $value
     * @throws  Zend_Cloud_Infrastructure_Exception
     */
    public function offsetSet($offset, $value)
    {
        #require_once 'Zend/Cloud/Infrastructure/Exception.php';
        throw new Zend_Cloud_Infrastructure_Exception('You are trying to set read-only property');
    }

    /**
     * Throws exception because all values are read-only
     *
     * Implement ArrayAccess::offsetUnset()
     *
     * @param   int     $offset
     * @throws  Zend_Cloud_Infrastructure_Exception
     */
    public function offsetUnset($offset)
    {
        #require_once 'Zend/Cloud/Infrastructure/Exception.php';
        throw new Zend_Cloud_Infrastructure_Exception('You are trying to unset read-only property');
    }
}
