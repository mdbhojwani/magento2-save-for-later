<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Observer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Mdbhojwani\SaveForLater\Model\SaveForLaterFactory;

/**
 * Class AddToCart
 */
class AddToCart implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Mdbhojwani\SaveForLater\Model\SaveForLaterFactory
     */
    protected $saveForLaterFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param SaveForLaterFactory $saveForLaterFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        SaveForLaterFactory $saveForLaterFactory,
        ManagerInterface $messageManager
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->saveForLaterFactory = $saveForLaterFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $sharedSaveForLater = $this->checkoutSession->getSharedSaveForLater();
        $messages = $this->checkoutSession->getSaveforlaterPendingMessages();
        $urls = $this->checkoutSession->getSaveforlaterPendingUrls();
        $saveForLaterIds = $this->checkoutSession->getSaveforlaterIds();
        $singleSaveForLaterId = $this->checkoutSession->getSingleSaveforlaterId();

        if ($singleSaveForLaterId) {
            $saveForLaterIds = [$singleSaveForLaterId];
        }

        if (is_array($saveForLaterIds) && count($saveForLaterIds) && $request->getParam('saveforlater_next')) {
            $saveForLaterId = array_shift($saveForLaterIds);

            if ($this->customerSession->isLoggedIn()) {
                $saveForLater = $this->saveForLaterFactory->create()
                    ->loadByCustomerId($this->customerSession->getCustomerId(), true);
            } elseif ($sharedSaveForLater) {
                $saveForLater = $this->saveForLaterFactory->create()->loadByCode($sharedSaveForLater);
            } else {
                return;
            }

            $saveForLaters = $saveForLater->getItemCollection()->load();
            foreach ($saveForLaters as $saveForLaterItem) {
                if ($saveForLaterItem->getId() == $saveForLaterId) {
                    $saveForLaterItem->delete();
                }
            }
            $this->checkoutSession->setSaveForLaterIds($saveForLaterIds);
            $this->checkoutSession->setSingleSaveForLaterId(null);
        }

        if ($request->getParam('saveforlater_next') && count($urls)) {
            $url = array_shift($urls);
            $message = array_shift($messages);

            $this->checkoutSession->setSaveforlaterPendingUrls($urls);
            $this->checkoutSession->setSaveforlaterPendingMessages($messages);

            $this->messageManager->addError($message);

            $observer->getEvent()->getResponse()->setRedirect($url);
            $this->checkoutSession->setNoCartRedirect(true);
        }
    }
}
