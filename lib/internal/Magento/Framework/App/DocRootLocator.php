<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;

/**
 * This class calculates if document root is set to pub
 */
class DocRootLocator
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @deprecated 102.0.2
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param RequestInterface $request
     * @param ReadFactory $readFactory
     * @param Filesystem|null $filesystem
     */
    public function __construct(RequestInterface $request, ReadFactory $readFactory, ?Filesystem $filesystem = null)
    {
        $this->request = $request;
        $this->readFactory = $readFactory;
        $this->filesystem = $filesystem ?: ObjectManager::getInstance()->get(Filesystem::class);
    }

    /**
     * Returns true if doc root is pub/ and not BP
     *
     * @return bool
     */
    public function isPub()
    {
        $rootBasePath = $this->request->getServer('DOCUMENT_ROOT') ?? '';
        $readDirectory = $this->filesystem->getDirectoryRead(DirectoryList::ROOT);

        return (substr($rootBasePath, -\strlen('/pub')) === '/pub') && ! $readDirectory->isExist('setup');
    }
}
