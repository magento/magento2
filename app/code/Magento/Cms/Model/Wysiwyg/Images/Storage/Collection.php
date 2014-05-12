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
namespace Magento\Cms\Model\Wysiwyg\Images\Storage;

/**
 * Wysiwyg Images storage collection
 */
class Collection extends \Magento\Framework\Data\Collection\Filesystem
{
    /**
     * @var \Magento\Framework\App\Filesystem
     */
    protected $_filesystem;

    /**
     * @param \Magento\Core\Model\EntityFactory $entityFactory
     * @param \Magento\Framework\App\Filesystem $filesystem
     */
    public function __construct(\Magento\Core\Model\EntityFactory $entityFactory, \Magento\Framework\App\Filesystem $filesystem)
    {
        $this->_filesystem = $filesystem;
        parent::__construct($entityFactory);
    }

    /**
     * Generate row
     *
     * @param string $filename
     * @return array
     */
    protected function _generateRow($filename)
    {
        $filename = preg_replace('~[/\\\]+~', '/', $filename);
        $path = $this->_filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::MEDIA_DIR);
        return array(
            'filename' => $filename,
            'basename' => basename($filename),
            'mtime' => $path->stat($path->getRelativePath($filename))['mtime']
        );
    }
}
