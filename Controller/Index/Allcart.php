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

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\App\Action\Context;
use Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface;
use Mdbhojwani\SaveForLater\Model\ItemCarrier;
use Magento\Framework\Controller\ResultFactory;

/**
 * Action Add All to Cart
 */
class Allcart extends \Mdbhojwani\SaveForLater\Controller\AbstractIndex implements HttpPostActionInterface
{
    /**
     * @var SaveForLaterProviderInterface
     */
    protected $saveForLaterProvider;

    /**
     * @var \Mdbhojwani\SaveForLater\Model\ItemCarrier
     */
    protected $itemCarrier;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @param Context $context
     * @param SaveForLaterProviderInterface $saveForLaterProvider
     * @param Validator $formKeyValidator
     * @param ItemCarrier $itemCarrier
     */
    public function __construct(
        Context $context,
        SaveForLaterProviderInterface $saveForLaterProvider,
        Validator $formKeyValidator,
        ItemCarrier $itemCarrier
    ) {
        $this->saveForLaterProvider = $saveForLaterProvider;
        $this->formKeyValidator = $formKeyValidator;
        $this->itemCarrier = $itemCarrier;
        parent::__construct($context);
    }

    /**
     * Add all items from SaveForLater to shopping cart
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $resultForward->forward('noroute');
            return $resultForward;
        }

        $saveForLater = $this->saveForLaterProvider->getSaveForLater();
        if (!$saveForLater) {
            $resultForward->forward('noroute');
            return $resultForward;
        }
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirectUrl = $this->itemCarrier->moveAllToCart($saveForLater, $this->getRequest()->getParam('qty'));
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }
}
