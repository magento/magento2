<?php
/**
 * Magento application action
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

interface ActionInterface
{
    const FLAG_NO_DISPATCH = 'no-dispatch';

    const FLAG_NO_POST_DISPATCH = 'no-postDispatch';

    const FLAG_NO_DISPATCH_BLOCK_EVENT = 'no-beforeGenerateLayoutBlocksDispatch';

    const PARAM_NAME_BASE64_URL = 'r64';

    const PARAM_NAME_URL_ENCODED = 'uenc';

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws Action\NotFoundException
     */
    public function dispatch(RequestInterface $request);

    /**
     * Get Response object
     *
     * @return ResponseInterface
     */
    public function getResponse();
}
