<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Newsletter\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class Template
 * Template fixture
 */
class Template extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Newsletter\Test\Repository\Template';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Newsletter\Test\Handler\Template\TemplateInterface';

    protected $defaultDataSet = [
        'code' => 'TemplateName%isolation%',
        'subject' => 'TemplateSubject%isolation%',
        'sender_name' => 'SenderName%isolation%',
        'sender_email' => 'SenderName%isolation%@example.com',
        'text' => 'Some text %isolation%',
    ];

    protected $id = [
        'attribute_code' => 'template_id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $code = [
        'attribute_code' => 'template_code',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $text = [
        'attribute_code' => 'template_text',
        'backend_type' => 'text',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $text_preprocessed = [
        'attribute_code' => 'template_text_preprocessed',
        'backend_type' => 'text',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $styles = [
        'attribute_code' => 'template_styles',
        'backend_type' => 'text',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $type = [
        'attribute_code' => 'template_type',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $subject = [
        'attribute_code' => 'template_subject',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $sender_name = [
        'attribute_code' => 'template_sender_name',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $sender_email = [
        'attribute_code' => 'template_sender_email',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $actual = [
        'attribute_code' => 'template_actual',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '1',
        'input' => '',
    ];

    protected $added_at = [
        'attribute_code' => 'added_at',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $modified_at = [
        'attribute_code' => 'modified_at',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    public function getId()
    {
        return $this->getData('id');
    }

    public function getCode()
    {
        return $this->getData('code');
    }

    public function getText()
    {
        return $this->getData('text');
    }

    public function getTextPreprocessed()
    {
        return $this->getData('text_preprocessed');
    }

    public function getStyles()
    {
        return $this->getData('styles');
    }

    public function getType()
    {
        return $this->getData('type');
    }

    public function getSubject()
    {
        return $this->getData('subject');
    }

    public function getSenderName()
    {
        return $this->getData('sender_name');
    }

    public function getSenderEmail()
    {
        return $this->getData('sender_email');
    }

    public function getActual()
    {
        return $this->getData('actual');
    }

    public function getAddedAt()
    {
        return $this->getData('added_at');
    }

    public function getModifiedAt()
    {
        return $this->getData('modified_at');
    }
}
