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

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface;

/**
 * SaveForLater UpdateItemOptions Controller
 */
class UpdateItemOptions extends \Mdbhojwani\SaveForLater\Controller\AbstractIndex implements Action\HttpPostActionInterface
{
    /**
     * @var SaveForLaterProviderInterface
     */
    protected $saveForLaterProvider;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @param Action\Context $context
     * @param Session $customerSession
     * @param SaveForLaterProviderInterface $saveForLaterProvider
     * @param ProductRepositoryInterface $productRepository
     * @param Validator $formKeyValidator
     */
    public function __construct(
        Action\Context $context,
        Session $customerSession,
        SaveForLaterProviderInterface $saveForLaterProvider,
        ProductRepositoryInterface $productRepository,
        Validator $formKeyValidator
    ) {
        $this->_customerSession = $customerSession;
        $this->saveForLaterProvider = $saveForLaterProvider;
        $this->productRepository = $productRepository;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    /**
     * Action to accept new configuration for a SaveForLater item
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }

        $productId = (int)$this->getRequest()->getParam('product');
        if (!$productId) {
            $resultRedirect->setPath('*/');
            return $resultRedirect;
        }

        try {
            $product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            $product = null;
        }

        if (!$product || !$product->isVisibleInCatalog()) {
            $this->messageManager->addErrorMessage(__('We can\'t specify a product.'));
            $resultRedirect->setPath('*/');
            return $resultRedirect;
        }

        try {
            $id = (int)$this->getRequest()->getParam('id');
            /* @var \Mdbhojwani\SaveForLater\Model\Item */
            $item = $this->_objectManager->create(\Mdbhojwani\SaveForLater\Model\Item::class);
            $item->load($id);
            $saveForLater = $this->saveForLaterProvider->getSaveForLater($item->getSaveforlaterId());
            if (!$saveForLater) {
                $resultRedirect->setPath('*/');
                return $resultRedirect;
            }

            $buyRequest = new \Magento\Framework\DataObject($this->getRequest()->getParams());

            $saveForLater->updateItem($id, $buyRequest)->save();

            $this->_objectManager->get(\Mdbhojwani\SaveForLater\Helper\Data::class)->calculate();
            $this->_eventManager->dispatch(
                'saveforlater_update_item',
                ['saveforlater' => $saveForLater, 'product' => $product, 'item' => $saveForLater->getItem($id)]
            );

            $this->_objectManager->get(\Mdbhojwani\SaveForLater\Helper\Data::class)->calculate();

            $message = __('%1 has been updated in the save for later.', $product->getName());
            $this->messageManager->addSuccessMessage($message);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t update the save for later right now.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
        $resultRedirect->setPath('*/*', ['saveforlater_id' => $saveForLater->getId()]);
        return $resultRedirect;
    }
}
