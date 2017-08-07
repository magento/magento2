<?php
/**
 * Massaction key processor
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App\Action\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Backend\App\AbstractAction;

/**
 * Class \Magento\Backend\App\Action\Plugin\MassactionKey
 *
 */
class MassactionKey
{
    /**
     * Process massaction key
     *
     * @param AbstractAction $subject
     * @param RequestInterface $request
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.2.0
     */
    public function beforeDispatch(AbstractAction $subject, RequestInterface $request)
    {
        $key = $request->getPost('massaction_prepare_key');
        if ($key) {
            $postData = $request->getPost($key);
            $value = is_array($postData) ? $postData : explode(',', $postData);
            $request->setPostValue($key, $value ? $value : null);
        }
    }
}
