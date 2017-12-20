<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

use Magento\Setup\Model\Declaration\Schema\Dto\ElementInterface;

/**
 * This class holds history about element modifications
 */
class ElementHistory
{
    /**
     * @var ElementInterface
     */
    private $new;

    /**
     * @var ElementInterface
     */
    private $old;

    /**
     * @param ElementInterface $new
     * @param ElementInterface $old
     */
    public function __construct(ElementInterface $new, ElementInterface $old = null)
    {
        $this->new = $new;
        $this->old = $old;
    }

    /**
     * Retrieve element, that exists before we run installation
     *
     * @return ElementInterface | null
     */
    public function getOld()
    {
        return $this->old;
    }

    /**
     * Retrieve element, that comes from configuration
     *
     * @return ElementInterface
     */
    public function getNew()
    {
        return $this->new;
    }
}
