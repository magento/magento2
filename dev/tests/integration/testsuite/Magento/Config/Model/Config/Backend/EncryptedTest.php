<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Config\Model\Config\Backend;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class EncryptedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoDbIsolation enabled
     */
    public function testEncryptionSave()
    {
        $originalValue = '1Password';

        /** @var $model \Magento\Config\Model\Config\Backend\Encrypted */
        $model = Bootstrap::getObjectManager()->create('Magento\Config\Model\Config\Backend\Encrypted');
        $model->setPath('carriers/usps/password');
        $model->setScopeId(0);
        $model->setScope('default');
        $model->setScopeCode('');
        $model->setValue($originalValue);
        $model->save();

        // Pass in the obscured value
        $model->setPath('carriers/usps/password');
        $model->setScopeId(0);
        $model->setScope('default');
        $model->setScopeCode('');
        $model->setValue('*****');
        $model->save();

        //Verify original value is not changed for obscured value
        $value = $model->load($model->getId())->getValue();
        $this->assertEquals($originalValue, $value, 'Original value is not expected to change.');

        // Verify if actual value is changed
        $changedValue = 'newPassword';

        $model->setPath('carriers/usps/password');
        $model->setScopeId(0);
        $model->setScope('default');
        $model->setScopeCode('');
        $model->setValue($changedValue);
        $model->save();

        //Verify original value is changed
        $value = $model->load($model->getId())->getValue();
        $this->assertEquals($changedValue, $value, 'Original value is expected to change.');
    }
}
