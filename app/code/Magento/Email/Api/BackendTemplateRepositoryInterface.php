<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Email\Api;

interface BackendTemplateRepositoryInterface
{
    
    /**
     * @param \Magento\Framework\Mail\TemplateInterface $template
     *
     * @return mixed
     */
    public function save(\Magento\Framework\Mail\TemplateInterface $template);
}
