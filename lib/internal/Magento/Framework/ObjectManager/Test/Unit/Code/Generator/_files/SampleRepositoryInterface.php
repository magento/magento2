<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Code\Generator;

interface SampleRepositoryInterface
{
    public function save(\Magento\Framework\ObjectManager\Code\Generator\SampleInterface $entity);

    public function get($id);

    public function deleteById($id);

    public function delete(\Magento\Framework\ObjectManager\Code\Generator\SampleInterface $entity);
}
