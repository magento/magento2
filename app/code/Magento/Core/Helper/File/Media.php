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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Helper\File;

/**
 * Class Media
 */
class Media extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\App\Filesystem
     */
    protected $filesystem;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->_date = $date;
        $this->filesystem = $filesystem;
    }

    /**
     * Collect file info
     *
     * Return array(
     *  filename    => string
     *  content     => string|bool
     *  update_time => string
     *  directory   => string
     *
     * @param string $mediaDirectory
     * @param string $path
     * @return array
     * @throws \Magento\Framework\Model\Exception
     */
    public function collectFileInfo($mediaDirectory, $path)
    {
        $path = ltrim($path, '\\/');
        $fullPath = $mediaDirectory . '/' . $path;

        $dir = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::MEDIA_DIR);
        $relativePath = $dir->getRelativePath($fullPath);
        if (!$dir->isFile($relativePath)) {
            throw new \Magento\Framework\Model\Exception(__('File %1 does not exist', $fullPath));
        }
        if (!$dir->isReadable($relativePath)) {
            throw new \Magento\Framework\Model\Exception(__('File %1 is not readable', $fullPath));
        }

        $path = str_replace(array('/', '\\'), '/', $path);
        $directory = dirname($path);
        if ($directory == '.') {
            $directory = null;
        }

        return array(
            'filename' => basename($path),
            'content' => $dir->readFile($relativePath),
            'update_time' => $this->_date->date(),
            'directory' => $directory
        );
    }
}
