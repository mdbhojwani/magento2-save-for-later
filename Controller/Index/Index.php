<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 */
class Index extends \Mdbhojwani\SaveForLater\Controller\AbstractIndex
{
    /**
     * @var \Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface
     */
    protected $saveForLaterProvider;

    /**
     * @param Action\Context $context
     * @param \Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface $saveForLaterProvider
     */
    public function __construct(
        Action\Context $context,
        \Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface $saveForLaterProvider
    ) {
        $this->saveForLaterProvider = $saveForLaterProvider;
        parent::__construct($context);
    }

    /**
     * Display customer SaveForLater
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->saveForLaterProvider->getSaveForLater()) {
            throw new NotFoundException(__('Page not found.'));
        }
        /** @var \Magento\Framework\View\Result\Page resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }
}
