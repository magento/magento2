<?php
/**
 * Magento filesystem stream mode
 *
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Filesystem\Stream;

class Mode
{
    /**
     * A stream mode as for the use of fopen()
     *
     * @var string
     */
    protected $_mode;

    /**
     * Base mode (e.g "r", "w", "a")
     *
     * @var string
     */
    protected $_base;

    /**
     * Is mode has plus (e.g. "w+")
     *
     * @var string
     */
    protected $_plus;

    /**
     * Additional mode of stream (e.g. "rb")
     *
     * @var string
     */
    protected $_flag;

    /**
     * Constructor
     *
     * @param string $mode
     */
    public function __construct($mode)
    {
        $this->_mode = $mode;

        $mode = substr($mode, 0, 3);
        $rest = substr($mode, 1);

        $this->_base = substr($mode, 0, 1);
        $this->_plus = false !== strpos($rest, '+');
        $this->_flag = trim($rest, '+');
    }

    /**
     * Returns the underlying mode
     *
     * @return string
     */
    public function getMode()
    {
        return $this->_mode;
    }

    /**
     * Indicates whether the mode allows to read
     *
     * @return bool
     */
    public function isReadAllowed()
    {
        if ($this->_plus) {
            return true;
        }

        return 'r' === $this->_base;
    }

    /**
     * Checks whether the mode allows to write.
     *
     * @return bool
     */
    public function isWriteAllowed()
    {
        if ($this->_plus) {
            return true;
        }

        return 'r' !== $this->_base;
    }

    /**
     * Checks whether the mode allows to open an existing file.
     *
     * @return bool
     */
    public function isExistingFileOpenAllowed()
    {
        return 'x' !== $this->_base;
    }

    /**
     * Checks whether the mode allows to create a new file.
     *
     * @return bool
     */
    public function isNewFileOpenAllowed()
    {
        return 'r' !== $this->_base;
    }

    /**
     * Indicates whether the mode implies to delete the existing content of the file when it already exists
     *
     * @return bool
     */
    public function isExistingContentDeletionImplied()
    {
        return 'w' === $this->_base;
    }

    /**
     * Indicates whether the mode implies positioning the cursor at the beginning of the file
     *
     * @return bool
     */
    public function isPositioningCursorAtTheBeginningImplied()
    {
        return 'a' !== $this->_base;
    }

    /**
     * Indicates whether the mode implies positioning the cursor at the end of the file
     *
     * @return bool
     */
    public function isPositioningCursorAtTheEndImplied()
    {
        return 'a' === $this->_base;
    }

    /**
     * Indicates whether the stream is in binary mode
     *
     * @return bool
     */
    public function isBinary()
    {
        return 'b' === $this->_flag;
    }
}
