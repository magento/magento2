<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Ui\Component\Listing\Column\Online\Website;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\System\Store;

/**
 * Website options for customers online grid
 */
class Options implements OptionSourceInterface
{
    /**
     * @var Store
     */
    private Store $systemStore;

    /**
     * @param Store $systemStore
     */
    public function __construct(Store $systemStore)
    {
        $this->systemStore = $systemStore;
    }

    /**
     * Get website options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return $this->systemStore->getWebsiteValuesForForm();
    }
}
