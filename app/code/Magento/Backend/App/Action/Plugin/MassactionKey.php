<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\App\Action\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Backend\App\AbstractAction;

/**
 * Massaction key processor
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
     */
    public function beforeDispatch(AbstractAction $subject, RequestInterface $request)
    {
        $key = $request->getPost('massaction_prepare_key');
        if ($key) {
            $postData = $request->getPost($key);
            $value = is_array($postData) ? $postData : explode(',', $postData ?? '');
            $request->setPostValue($key, $value ? $value : null);
        }
    }
}
