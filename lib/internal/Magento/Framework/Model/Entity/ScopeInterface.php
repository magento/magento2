<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Model\Entity;

/**
 * Interface ScopeInterface
 *
 * @api
 */
interface ScopeInterface
{
    /**
     * @return string
     */
    public function getValue();

    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @return ScopeInterface|null
     */
    public function getFallback();
}
