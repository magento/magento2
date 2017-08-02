<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Fallback\Rule;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\Framework\View\Design\Fallback\Rule\SimpleFactory
 *
 * @since 2.0.0
 */
class SimpleFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.0.0
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @since 2.0.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create rule instance
     *
     * @param array $data
     * @return \Magento\Framework\View\Design\Fallback\Rule\Simple
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create(\Magento\Framework\View\Design\Fallback\Rule\Simple::class, $data);
    }
}
