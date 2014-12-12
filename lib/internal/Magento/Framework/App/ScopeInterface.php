<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App;

interface ScopeInterface
{
    /**
     * Default scope type
     */
    const SCOPE_DEFAULT = 'default';

    /**
     * Retrieve scope code
     *
     * @return string
     */
    public function getCode();

    /**
     * Get scope identifier
     *
     * @return  int
     */
    public function getId();
}
