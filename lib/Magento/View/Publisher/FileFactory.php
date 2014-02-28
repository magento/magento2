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

namespace Magento\View\Publisher;

use Magento\ObjectManager;

/**
 * Publisher file factory
 */
class FileFactory
{
    /**
     * Default publisher file class
     */
    const DEFAULT_FILE_INSTANCE_CLASS = 'Magento\View\Publisher\File';

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $instanceName;

    /**
     * @var array
     */
    protected $publisherFileTypes = [
        'css' => 'Magento\View\Publisher\CssFile'
    ];

    /**
     * @param ObjectManager $objectManager
     * @param string $instanceName
     */
    public function __construct(ObjectManager $objectManager, $instanceName = self::DEFAULT_FILE_INSTANCE_CLASS)
    {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Return newly created instance of a publisher file
     *
     * @param string $filePath
     * @param array $viewParams
     * @param null|string $sourcePath
     * @return FileInterface
     * @throws \UnexpectedValueException
     */
    public function create($filePath, array $viewParams, $sourcePath = null)
    {
        $instanceName = $this->instanceName;
        $extension = $this->getExtension($filePath);
        if (isset($this->publisherFileTypes[$extension])) {
            $instanceName = $this->publisherFileTypes[$extension];
        }
        $publisherFile = $this->objectManager->create(
            $instanceName,
            [
                'filePath'   => $filePath,
                'viewParams' => $viewParams,
                'sourcePath' => $sourcePath
            ]
        );

        if (!$publisherFile instanceof FileInterface) {
            throw new \UnexpectedValueException("$instanceName has to implement the publisher file interface.");
        }
        return $publisherFile;
    }

    /**
     * Get file extension by file path
     *
     * @param string $filePath
     * @return string
     */
    protected function getExtension($filePath)
    {
        return strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    }
}
