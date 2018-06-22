<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Code\Generator;

interface SampleRepositoryInterface
{
    /**
     * @param SampleInterface $entity
     *
     * @return mixed
     */
    public function save(\Magento\Framework\ObjectManager\Code\Generator\SampleInterface $entity);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function get($id);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function deleteById($id);

    /**
     * @param SampleInterface $entity
     *
     * @return mixed
     */
    public function delete(\Magento\Framework\ObjectManager\Code\Generator\SampleInterface $entity);
}
