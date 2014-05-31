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

namespace Magento\Framework\App\View\Deployment\Version\Storage;

/**
 * Persistence of deployment version of static files in a local file
 */
class File implements \Magento\Framework\App\View\Deployment\Version\StorageInterface
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $directory;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param string $directoryCode
     * @param string $fileName
     */
    public function __construct(
        \Magento\Framework\App\Filesystem $filesystem,
        $directoryCode,
        $fileName
    ) {
        $this->directory = $filesystem->getDirectoryWrite($directoryCode);
        $this->fileName = $fileName;
    }

    /**
     * {@inheritdoc}
     */
    public function load()
    {
        try {
            return $this->directory->readFile($this->fileName);
        } catch (\Magento\Framework\Filesystem\FilesystemException $e) {
            throw new \UnexpectedValueException(
                'Unable to retrieve deployment version of static files from the file system.',
                0,
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save($data)
    {
        $this->directory->writeFile($this->fileName, $data, 'w');
    }
}
