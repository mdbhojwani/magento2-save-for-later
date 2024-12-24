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
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface;
use Mdbhojwani\SaveForLater\Model\Item;
use Mdbhojwani\SaveForLater\Model\Product\AttributeValueProvider;

/**
 * SaveForLater Remove Controller
 */
class Remove extends \Mdbhojwani\SaveForLater\Controller\AbstractIndex implements Action\HttpPostActionInterface
{
    /**
     * @var SaveForLaterProviderInterface
     */
    protected $saveForLaterProvider;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var AttributeValueProvider
     */
    private $attributeValueProvider;

    /**
     * @param Action\Context $context
     * @param SaveForLaterProviderInterface $saveForLaterProvider
     * @param Validator $formKeyValidator
     * @param AttributeValueProvider|null $attributeValueProvider
     */
    public function __construct(
        Action\Context $context,
        SaveForLaterProviderInterface $saveForLaterProvider,
        Validator $formKeyValidator,
        AttributeValueProvider $attributeValueProvider = null
    ) {
        $this->saveForLaterProvider = $saveForLaterProvider;
        $this->formKeyValidator = $formKeyValidator;
        $this->attributeValueProvider = $attributeValueProvider
            ?: \Magento\Framework\App\ObjectManager::getInstance()->get(AttributeValueProvider::class);
        parent::__construct($context);
    }

    /**
     * Remove item
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws NotFoundException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/*/');
        }

        $id = (int)$this->getRequest()->getParam('item');
        /** @var Item $item */
        $item = $this->_objectManager->create(Item::class)->load($id);
        if (!$item->getId()) {
            throw new NotFoundException(__('Page not found.'));
        }
        $saveForLater = $this->saveForLaterProvider->getSaveForLater($item->getSaveforlaterId());
        if (!$saveForLater) {
            throw new NotFoundException(__('Page not found.'));
        }
        try {
            $item->delete();
            $saveForLater->save();
            $productName = $this->attributeValueProvider
                ->getRawAttributeValue($item->getProductId(), 'name');
            $this->messageManager->addComplexSuccessMessage(
                'removeSaveForLaterItemSuccessMessage',
                [
                    'product_name' => $productName,
                ]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                __('We can\'t delete the item from Save for Later right now because of an error: %1.', $e->getMessage())
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We can\'t delete the item from the Save for Later right now.'));
        }

        $this->_objectManager->get(\Mdbhojwani\SaveForLater\Helper\Data::class)->calculate();
        $refererUrl = $this->_redirect->getRefererUrl();
        if ($refererUrl) {
            $redirectUrl = $refererUrl;
        } else {
            $redirectUrl = $this->_redirect->getRedirectUrl($this->_url->getUrl('*/*'));
        }
        $resultRedirect->setUrl($redirectUrl);
        return $resultRedirect;
    }
}
