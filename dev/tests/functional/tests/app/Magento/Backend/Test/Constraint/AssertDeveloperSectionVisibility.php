<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Backend\Test\Page\Adminhtml\SystemConfigEdit;

/**
 * Assert that all groups in Developer section is not present in production mode except debug group "Log to File" field.
 */
class AssertDeveloperSectionVisibility extends AbstractConstraint
{
    /**
     * List of groups not visible in production mode.
     *
     * @var array
     */
    private $groups = [
        'front_end_development_workflow',
        'restrict',
        'template',
        'translate_inline',
        'js',
        'css',
        'image',
        'static',
        'grid',
    ];

    /**
     * Assert all groups in Developer section is not present in production mode except debug group "Log to File" field.
     *
     * @param SystemConfigEdit $configEdit
     * @return void
     */
    public function processAssert(SystemConfigEdit $configEdit)
    {
        $configEdit->open();
        if ($_ENV['mage_mode'] === 'production') {
            foreach ($this->groups as $group) {
<<<<<<< HEAD
                \PHPUnit\Framework\Assert::assertFalse(
=======
                \PHPUnit_Framework_Assert::assertFalse(
>>>>>>> upstream/2.2-develop
                    $configEdit->getForm()->isGroupVisible('dev', $group),
                    sprintf('%s group should be hidden in production mode.', $group)
                );
            }
<<<<<<< HEAD
            \PHPUnit\Framework\Assert::assertTrue(
=======
            \PHPUnit_Framework_Assert::assertTrue(
>>>>>>> upstream/2.2-develop
                $configEdit->getForm()->getGroup('dev', 'debug')->isFieldVisible('dev', 'debug_debug', 'logging'),
                '"Log to File" should be presented in production mode.'
            );
        } else {
            foreach ($this->groups as $group) {
<<<<<<< HEAD
                \PHPUnit\Framework\Assert::assertTrue(
=======
                \PHPUnit_Framework_Assert::assertTrue(
>>>>>>> upstream/2.2-develop
                    $configEdit->getForm()->isGroupVisible('dev', $group),
                    sprintf('%s group should be visible in developer mode.', $group)
                );
            }
<<<<<<< HEAD
            \PHPUnit\Framework\Assert::assertTrue(
=======
            \PHPUnit_Framework_Assert::assertTrue(
>>>>>>> upstream/2.2-develop
                $configEdit->getForm()->isGroupVisible('dev', 'debug'),
                'Debug group should be visible in developer mode.'
            );
        }
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Developer section has correct visibility.';
    }
}
