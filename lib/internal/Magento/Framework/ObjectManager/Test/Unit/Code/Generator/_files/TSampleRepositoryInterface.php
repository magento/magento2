<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Code\Generator;

interface TSampleRepositoryInterface
{
    /**
     * @param int $id
<<<<<<< HEAD
     *
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @return TSampleInterface
     */
    public function get(int $id) : \Magento\Framework\ObjectManager\Code\Generator\TSampleInterface;

    /**
     * @param TSampleInterface $entity
<<<<<<< HEAD
     *
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @return bool
     */
    public function delete(\Magento\Framework\ObjectManager\Code\Generator\TSampleInterface $entity) : bool;

    /**
     * @param TSampleInterface $entity
<<<<<<< HEAD
     *
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @return TSampleInterface
     */
    public function save(\Magento\Framework\ObjectManager\Code\Generator\TSampleInterface $entity)
        : \Magento\Framework\ObjectManager\Code\Generator\TSampleInterface;
}
