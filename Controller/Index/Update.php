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
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Controller for updating SaveForLaters
 */
class Update extends \Mdbhojwani\SaveForLater\Controller\AbstractIndex implements HttpPostActionInterface
{
    /**
     * @var \Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface
     */
    protected $saveForLaterProvider;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Mdbhojwani\SaveForLater\Model\LocaleQuantityProcessor
     */
    protected $quantityProcessor;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface $saveForLaterProvider
     * @param \Mdbhojwani\SaveForLater\Model\LocaleQuantityProcessor $quantityProcessor
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface $saveForLaterProvider,
        \Mdbhojwani\SaveForLater\Model\LocaleQuantityProcessor $quantityProcessor
    ) {
        $this->_formKeyValidator = $formKeyValidator;
        $this->saveForLaterProvider = $saveForLaterProvider;
        $this->quantityProcessor = $quantityProcessor;
        parent::__construct($context);
    }

    /**
     * Update SaveForLater item comments
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws NotFoundException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
        $saveForLater = $this->saveForLaterProvider->getSaveForLater();
        if (!$saveForLater) {
            throw new NotFoundException(__('Page not found.'));
        }

        $post = $this->getRequest()->getPostValue();
        $resultRedirect->setPath('*', ['saveforlater_id' => $saveForLater->getId()]);
        if (!$post) {
            return $resultRedirect;
        }

        if (isset($post['description']) && is_array($post['description'])) {
            $updatedItems = 0;

            foreach ($post['description'] as $itemId => $description) {
                $item = $this->_objectManager->create(\Mdbhojwani\SaveForLater\Model\Item::class)->load($itemId);
                if ($item->getSaveforlaterId() != $saveForLater->getId()) {
                    continue;
                }

                // Extract new values
                $description = (string)$description;

                if ($description == $this->_objectManager->get(
                    \Mdbhojwani\SaveForLater\Helper\Data::class
                )->defaultCommentString()
                ) {
                    $description = '';
                }

                $qty = null;
                if (isset($post['qty'][$itemId])) {
                    $qty = $this->quantityProcessor->process($post['qty'][$itemId]);
                }
                if ($qty === null) {
                    $qty = $item->getQty();
                    if (!$qty) {
                        $qty = 1;
                    }
                } elseif (0 == $qty) {
                    try {
                        $item->delete();
                    } catch (\Exception $e) {
                        $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                        $this->messageManager->addErrorMessage(__('We can\'t delete item from save for later right now.'));
                    }
                }

                // Check that we need to save
                if ($item->getDescription() == $description && $item->getQty() == $qty) {
                    continue;
                }
                try {
                    $item->setDescription($description)->setQty($qty)->save();
                    $this->messageManager->addSuccessMessage(
                        __('%1 has been updated in the save for later.', $item->getProduct()->getName())
                    );
                    $updatedItems++;
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Can\'t save description %1',
                            $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($description)
                        )
                    );
                }
            }

            // save SaveForLater model for setting date of last update
            if ($updatedItems) {
                try {
                    $saveForLater->save();
                    $this->_objectManager->get(\Mdbhojwani\SaveForLater\Helper\Data::class)->calculate();
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('Can\'t update save for later'));
                }
            }
        }

        if (isset($post['save_and_share'])) {
            $resultRedirect->setPath('*/*/share', ['saveforlater_id' => $saveForLater->getId()]);
        }

        return $resultRedirect;
    }
}
