<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Magento application action controller type. Every action controller in Application should implement this interface.
 *
 * @api
 * @since 2.0.0
 */
interface ActionInterface
{
    const FLAG_NO_DISPATCH = 'no-dispatch';

    const FLAG_NO_POST_DISPATCH = 'no-postDispatch';

    const FLAG_NO_DISPATCH_BLOCK_EVENT = 'no-beforeGenerateLayoutBlocksDispatch';

    const PARAM_NAME_BASE64_URL = 'r64';

    const PARAM_NAME_URL_ENCODED = 'uenc';

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     * @since 2.0.0
     */
    public function execute();
}
