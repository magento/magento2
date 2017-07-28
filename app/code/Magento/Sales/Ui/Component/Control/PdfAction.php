<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\Control;

use Magento\Ui\Component\Control\Action;

/**
 * Class PdfAction
 * @since 2.1.0
 */
class PdfAction extends Action
{
    /**
     * Prepare
     *
     * @return void
     * @since 2.1.0
     */
    public function prepare()
    {
        $config = $this->getConfiguration();
        $context = $this->getContext();
        $config['url'] = $context->getUrl(
            $config['pdfAction'],
            ['order_id' => $context->getRequestParam('order_id')]
        );
        $this->setData('config', (array)$config);
        parent::prepare();
    }
}
