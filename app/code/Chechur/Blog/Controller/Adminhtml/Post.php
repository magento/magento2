<?php
declare(strict_types=1);

namespace Chechur\Blog\Controller\Adminhtml;

use Chechur\Blog\Api\PostRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;

abstract class Post extends Action

{
    /**
     * @var string
     */
    const ACTION_RESOURCE = 'Chechur_blog::post';
    /**
     * post factory
     *
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * date filter
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Registry $registry
     * @param PostRepositoryInterface $postRepository
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        PostRepositoryInterface $postRepository,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context

    )
    {
        $this->coreRegistry = $registry;
        $this->postRepository = $postRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->dateFilter = $dateFilter;
        parent::__construct($context);
    }

    /**
     * filter dates
     *
     * @param array $data
     * @return array
     */
    public function filterData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            ['dob' => $this->dateFilter],
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        if (isset($data['awards'])) {
            if (is_array($data['awards'])) {
                $data['awards'] = implode(',', $data['awards']);
            }
        }
        return $data;
    }

}
