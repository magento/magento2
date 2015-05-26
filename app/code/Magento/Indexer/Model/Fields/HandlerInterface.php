<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Model\Fields;

interface HandlerInterface
{
    /**
     * @param array $data
     * @return []
     */
    public function handle($data = []);
}
