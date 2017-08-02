<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure;

/**
 * @api
 * @since 2.0.0
 */
interface SearchInterface
{
    /**
     * Find element by path
     *
     * @param string $path
     * @return ElementInterface|null
     * @since 2.0.0
     */
    public function getElement($path);
}
