<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

interface FeedImporterInterface
{

    /**
     * @throws \Magento\Framework\Exception\FeedImporterException
     * @param  array  $data
     * @param  string $format
     * @return FeedInterface
     */
    public function importArray(array $data, $format = 'atom');
}
