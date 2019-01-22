<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Review\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Review section
 */
class Review implements SectionSourceInterface
{
    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $reviewSession;

    /**
     * @param \Magento\Framework\Session\Generic $reviewSession
     */
    public function __construct(\Magento\Framework\Session\Generic $reviewSession)
    {
        $this->reviewSession = $reviewSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData(): array
    {
        return (array)$this->reviewSession->getFormData(true) + ['nickname' => '','title' => '', 'detail' => ''];
    }
}
