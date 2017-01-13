<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure;

interface SearchInterface
{
    /**
     * Find element by path
     *
     * @param string $path
     * @return ElementInterface|null
     */
    public function getElement($path);
}
