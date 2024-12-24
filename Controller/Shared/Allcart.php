<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Controller\Shared;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Mdbhojwani\SaveForLater\Model\ItemCarrier;

/**
 * SaveForLater Allcart Controller
 */
class Allcart extends Action implements HttpPostActionInterface
{
    /**
     * @var SaveForLaterProvider
     */
    protected $saveForLaterProvider;

    /**
     * @var ItemCarrier
     */
    protected $itemCarrier;

    /**
     * @param Context $context
     * @param SaveForLaterProvider $saveForLaterProvider
     * @param ItemCarrier $itemCarrier
     */
    public function __construct(
        Context $context,
        SaveForLaterProvider $saveForLaterProvider,
        ItemCarrier $itemCarrier
    ) {
        $this->saveForLaterProvider = $saveForLaterProvider;
        $this->itemCarrier = $itemCarrier;
        parent::__construct($context);
    }

    /**
     * Add all items from SaveForLater to shopping cart
     *
     * {@inheritDoc}
     */
    public function execute()
    {
        $saveForLater = $this->saveForLaterProvider->getSaveForLater();
        if (!$saveForLater) {
            /** @var Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }
        $redirectUrl = $this->itemCarrier->moveAllToCart($saveForLater, $this->getRequest()->getParam('qty'));
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($redirectUrl);

        return $resultRedirect;
    }
}
