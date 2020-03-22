<?php
/**
 * Massaction key processor
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\App\Action\Plugin;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;

class BackendActionMassactionKeyPlugin
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Process massaction key
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(ActionInterface $subject)
    {
        $key = $this->request->getPost('massaction_prepare_key');
        if ($key) {
            $postData = $this->request->getPost($key);
            $value = is_array($postData) ? $postData : explode(',', $postData);
            $this->request->setPostValue($key, $value ? $value : null);
        }
    }
}
