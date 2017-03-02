<?php
/**
 * Magento application action
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * @api
 */
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
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute();
}
