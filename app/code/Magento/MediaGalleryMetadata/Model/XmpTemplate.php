<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryMetadata\Model;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;

/**
 * XMP template provider
 */
class XmpTemplate
{
    private const XMP_TEMPLATE_FILENAME = 'default.xmp';

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var DriverInterface
     */
    private $driver;

    /**
     * @param Reader $moduleReader
     * @param DriverInterface $driver
     */
    public function __construct(Reader $moduleReader, DriverInterface $driver)
    {
        $this->moduleReader = $moduleReader;
        $this->driver = $driver;
    }

    /**
     * Get default XMP template
     *
     * @return string
     * @throws FileSystemException
     */
    public function get(): string
    {
        $etcDirectoryPath = $this->moduleReader->getModuleDir(
            Dir::MODULE_ETC_DIR,
            'Magento_MediaGalleryMetadata'
        );
        return $this->driver->fileGetContents(
            $etcDirectoryPath . '/' . self::XMP_TEMPLATE_FILENAME
        );
    }
}
