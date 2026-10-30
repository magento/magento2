<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Code\Generator;

interface SampleRepositoryInterface
{
    /**
     * @param SampleInterface $entity
     * @return mixed
     */
    public function save(\Magento\Framework\ObjectManager\Code\Generator\SampleInterface $entity);

    /**
     * @param $id
     * @return mixed
     */
    public function get($id);

    /**
     * @param $id
     * @return mixed
     */
    public function deleteById($id);

    /**
     * @param SampleInterface $entity
     * @return mixed
     */
    public function delete(\Magento\Framework\ObjectManager\Code\Generator\SampleInterface $entity);
}
