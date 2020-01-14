<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider;

/**
 * Fake entity object
 * @see \Magento\Ui\DataProvider\SearchResultFactoryTest
 */
class EntityFake
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $attributeFoo;

    /**
     * @var string
     */
    private $attributeBar;

    /**
     * @param int $id
     * @param string $attributeFoo
     * @param string $attributeBar
     */
    public function __construct(int $id, string $attributeFoo, string $attributeBar)
    {
        $this->id = $id;
        $this->attributeFoo = $attributeFoo;
        $this->attributeBar = $attributeBar;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getAttributeFoo(): string
    {
        return $this->attributeFoo;
    }

    /**
     * @return string
     */
    public function getAttributeBar(): string
    {
        return $this->attributeBar;
    }
}
