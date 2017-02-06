<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Controller\Result;

use Magento\Framework\ObjectManagerInterface;

class RedirectFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $instanceName;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = \Magento\Framework\Controller\Result\Redirect::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}
