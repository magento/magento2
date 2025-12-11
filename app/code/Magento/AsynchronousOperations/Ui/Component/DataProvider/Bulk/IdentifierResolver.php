<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\AsynchronousOperations\Ui\Component\DataProvider\Bulk;

use Magento\Framework\App\RequestInterface;

/**
 * Class IdentifierResolver
 */
class IdentifierResolver
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @return null|string
     */
    public function execute()
    {
        return $this->request->getParam('uuid');
    }
}
