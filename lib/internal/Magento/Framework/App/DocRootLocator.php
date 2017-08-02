<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\ReadFactory;

/**
 * This class calculates if document root is set to pub
 * @since 2.1.0
 */
class DocRootLocator
{
    /**
     * @var RequestInterface
     * @since 2.1.0
     */
    private $request;

    /**
     * @var ReadFactory
     * @since 2.1.0
     */
    private $readFactory;

    /**
     * @param RequestInterface $request
     * @param ReadFactory $readFactory
     * @since 2.1.0
     */
    public function __construct(RequestInterface $request, ReadFactory $readFactory)
    {
        $this->request = $request;
        $this->readFactory = $readFactory;
    }

    /**
     * Returns true if doc root is pub/ and not BP
     *
     * @return bool
     * @since 2.1.0
     */
    public function isPub()
    {
        $rootBasePath = $this->request->getServer('DOCUMENT_ROOT');
        $readDirectory = $this->readFactory->create(DirectoryList::ROOT);
        return (substr($rootBasePath, -strlen('/pub')) === '/pub') && !$readDirectory->isExist($rootBasePath . 'setup');
    }
}
