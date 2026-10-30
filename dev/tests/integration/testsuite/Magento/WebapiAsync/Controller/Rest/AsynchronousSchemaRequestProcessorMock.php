<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\WebapiAsync\Controller\Rest;

class AsynchronousSchemaRequestProcessorMock extends AsynchronousSchemaRequestProcessor
{
    /**
     * {@inheritdoc}
     */
    public function canProcess(\Magento\Framework\Webapi\Rest\Request $request)
    {
        return true;
    }
}
