<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Controller\Adminhtml\System\Design\Config;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Form\FormKey;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Class SaveTest @covers \Magento\Theme\Controller\Adminhtml\Design\Config\Save
 */
class SaveTest extends AbstractBackendController
{
    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @inheritdoc
     */
    protected $resource = 'Magento_Config::config_design';

    /**
     * @inheritdoc
     */
    protected $uri = 'backend/theme/design_config/save';

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->formKey = $this->_objectManager->get(
            FormKey::class
        );
        $this->httpMethod = Http::METHOD_POST;
    }

    /**
     * Test design configuration save valid values.
     *
     * @return void
     */
    public function testSave()
    {
        $params = $this->getRequestParams();
        $this->getRequest()->setParams($params);
        $error = '';
        try {
            $this->dispatch($this->uri);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        self::assertEmpty($error, $error);
    }

    /**
     * Provide test request params for testSave().
     *
     * @return array
     */
    private function getRequestParams()
    {
        return [
            'theme_theme_id' => '',
            'pagination_pagination_frame' => '5',
            'pagination_pagination_frame_skip' => '',
            'pagination_anchor_text_for_previous' => '',
            'pagination_anchor_text_for_next' => '',
            'head_default_title' => 'Magento Commerce',
            'head_title_prefix' => '',
            'head_title_suffix' => '',
            'head_default_description' => '',
            'head_default_keywords' => '',
            'head_includes' => '',
            'head_demonotice' => '0',
            'header_logo_width' => '',
            'header_logo_height' => '',
            'header_logo_alt' => '',
            'header_welcome' => 'Default welcome msg!',
            'footer_copyright' => 'Copyright © 2013-present Magento, Inc. All rights reserved.',
            'footer_absolute_footer' => '',
            'default_robots' => 'INDEX,FOLLOW',
            'custom_instructions' => '',
            'watermark_image_size' => '',
            'watermark_image_imageOpacity' => '',
            'watermark_image_position' => 'stretch',
            'watermark_small_image_size' => '',
            'watermark_small_image_imageOpacity' => '',
            'watermark_small_image_position' => 'stretch',
            'watermark_thumbnail_size' => '',
            'watermark_thumbnail_imageOpacity' => '',
            'watermark_thumbnail_position' => 'stretch',
            'email_logo_alt' => 'test',
            'email_logo_width' => '200',
            'email_logo_height' => '100',
            'email_header_template' => 'design_email_header_template',
            'email_footer_template' => 'design_email_footer_template',
            'watermark_swatch_image_size' => '',
            'watermark_swatch_image_imageOpacity' => '',
            'watermark_swatch_image_position' => 'stretch',
            'scope' => 'default',
            'form_key' => $this->formKey->getFormKey(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function testAclHasAccess()
    {
        $this->getRequest()->setParams(
            [
                'form_key' => $this->formKey->getFormKey()
            ]
        );

        parent::testAclHasAccess();
    }
}
