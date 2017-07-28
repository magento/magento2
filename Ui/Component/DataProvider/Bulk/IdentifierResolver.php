<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Ui\Component\DataProvider\Bulk;

use Magento\Framework\App\RequestInterface;

/**
 * Class IdentifierResolver
 * @since 2.2.0
 */
class IdentifierResolver
{
    /**
     * @var RequestInterface
     * @since 2.2.0
     */
    private $request;

    /**
     * @param RequestInterface $request
     * @since 2.2.0
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @return null|string
     * @since 2.2.0
     */
    public function execute()
    {
        return $this->request->getParam('uuid');
    }
}
