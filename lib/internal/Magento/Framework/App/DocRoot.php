<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\ReadFactory;

/**
 * This class calculates if document root is set to pub
 */
class DocRoot
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @param RequestInterface $request
     * @param ReadFactory $readFactory
     */
    public function __construct(RequestInterface $request, ReadFactory $readFactory)
    {
        $this->request = $request;
        $this->readFactory = $readFactory;
    }


    /**
     * Returns true if doc root is pub/ and not BP
     *
     * @param $dirToCheck
     * @param $missingDir
     *
     * @return bool
     */
    public function hasThisSubDir($dirToCheck, $missingDir)
    {
        $rootBasePath = $this->request->getServer('DOCUMENT_ROOT');
        $readDirectory = $this->readFactory->create(DirectoryList::ROOT);
        return strpos($rootBasePath, $dirToCheck) && !$readDirectory->isExist($rootBasePath + $missingDir);
    }
}
