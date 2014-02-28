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

namespace Magento\Less\PreProcessor\File;

/**
 * Less file list container
 */
class FileList implements \Iterator
{
    /**
     * @var LessFactory
     */
    protected $lessFactory;

    /**
     * Entry less file for this container
     *
     * @var Less
     */
    protected $initialFile;

    /**
     * @var Less[]
     */
    protected $files = [];

    /**
     * Constructor
     *
     * @param LessFactory $lessFactory
     * @param string $lessFilePath
     * @param array $viewParams
     * @throws \InvalidArgumentException
     */
    public function __construct(
        LessFactory $lessFactory,
        $lessFilePath = null,
        $viewParams = []
    ) {
        if (empty($lessFilePath) || empty($viewParams)) {
            throw new \InvalidArgumentException('FileList container must contain entry less file data');
        }
        $this->lessFactory = $lessFactory;
        $this->initialFile = $this->createFile($lessFilePath, $viewParams);
        $this->addFile($this->initialFile);
    }

    /**
     * Return entry less file for this container
     *
     * @return Less
     */
    public function getInitialFile()
    {
        return $this->initialFile;
    }

    /**
     * Return publication path of entry less file
     *
     * @return string
     */
    public function getPublicationPath()
    {
        return $this->initialFile->getPublicationPath();
    }

    /**
     * Add file to list
     *
     * @param Less $file
     * @return $this
     */
    public function addFile(Less $file)
    {
        $this->files[$file->getFileIdentifier()] = $file;
        return $this;
    }

    /**
     * Create instance of less file
     *
     * @param string $lessFilePath
     * @param array $viewParams
     * @return mixed
     */
    public function createFile($lessFilePath, $viewParams)
    {
        return $this->lessFactory->create(['filePath' => $lessFilePath, 'viewParams' => $viewParams]);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return current($this->files);
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        next($this->files);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return key($this->files);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return (bool) current($this->files);
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        reset($this->files);
    }
}
