<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Controller;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;

/**
 * SaveForLaterProvider Controller
 */
class SaveForLaterProvider implements SaveForLaterProviderInterface
{
    /**
     * @var \Mdbhojwani\SaveForLater\Model\SaveForLater
     */
    protected $saveForLater;

    /**
     * @var \Mdbhojwani\SaveForLater\Model\SaveForLaterFactory
     */
    protected $saveForLaterFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Mdbhojwani\SaveForLater\Model\SaveForLaterFactory $saveForLaterFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param RequestInterface $request
     */
    public function __construct(
        \Mdbhojwani\SaveForLater\Model\SaveForLaterFactory $saveForLaterFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        RequestInterface $request
    ) {
        $this->request = $request;
        $this->saveForLaterFactory = $saveForLaterFactory;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
    }

    /**
     * @inheritdoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getSaveForLater($saveForLaterId = null)
    {
        if ($this->saveForLater) {
            return $this->saveForLater;
        }
        try {
            if (!$saveForLaterId) {
                $saveForLaterId = $this->request->getParam('saveforlater_id');
            }
            $customerId = $this->customerSession->getCustomerId();
            $saveForLater = $this->saveForLaterFactory->create();

            if (!$saveForLaterId && !$customerId) {
                return $saveForLater;
            }

            if ($saveForLaterId) {
                $saveForLater->load($saveForLaterId);
            } elseif ($customerId) {
                $saveForLater->loadByCustomerId($customerId, true);
            }

            if (!$saveForLater->getId() || $saveForLater->getCustomerId() != $customerId) {
                throw new \Magento\Framework\Exception\NoSuchEntityException(
                    __('The requested save for later doesn\'t exist.')
                );
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return false;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t create the save for later right now.'));
            return false;
        }
        $this->saveForLater = $saveForLater;
        return $saveForLater;
    }
}
