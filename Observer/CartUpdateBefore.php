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

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mdbhojwani\SaveForLater\Helper\Data;
use Mdbhojwani\SaveForLater\Model\SaveForLater;
use Mdbhojwani\SaveForLater\Model\SaveForLaterFactory;

/**
 * Class CartUpdateBefore
 */
class CartUpdateBefore implements ObserverInterface
{
    /**
     * SaveForLater data
     *
     * @var Data
     */
    protected $saveForLaterData;

    /**
     * @var SaveForLaterFactory
     */
    protected $saveForLaterFactory;

    /**
     * @param Data $saveForLaterData
     * @param SaveForLaterFactory $saveForLaterFactory
     */
    public function __construct(
        Data $saveForLaterData,
        SaveForLaterFactory $saveForLaterFactory
    ) {
        $this->saveForLaterData = $saveForLaterData;
        $this->saveForLaterFactory = $saveForLaterFactory;
    }

    /**
     * Get customer SaveForLater model instance
     *
     * @param   int $customerId
     * @return  SaveForLater|false
     */
    protected function getSaveForLater($customerId)
    {
        if (!$customerId) {
            return false;
        }
        return $this->saveForLaterFactory->create()->loadByCustomerId($customerId, true);
    }

    /**
     * Check move quote item to SaveForLater request
     *
     * @param   Observer $observer
     * @return  $this
     */
    public function execute(Observer $observer)
    {
        $cart = $observer->getEvent()->getCart();
        $data = $observer->getEvent()->getInfo()->toArray();
        $productIds = [];

        $saveForLater = $this->getSaveForLater($cart->getQuote()->getCustomerId());
        if (!$saveForLater) {
            return $this;
        }

        /**
         * Collect product ids marked for move to SaveForLater
         */
        foreach ($data as $itemId => $itemInfo) {
            if (!empty($itemInfo['saveforlater']) && ($item = $cart->getQuote()->getItemById($itemId))) {
                $productId = $item->getProductId();
                $buyRequest = $item->getBuyRequest();

                if (array_key_exists('qty', $itemInfo) && is_numeric($itemInfo['qty'])) {
                    $buyRequest->setQty($itemInfo['qty']);
                }
                $saveForLater->addNewItem($productId, $buyRequest);

                $productIds[] = $productId;
                $cart->getQuote()->removeItem($itemId);
            }
        }

        if (count($productIds)) {
            $saveForLater->save();
            $this->saveForLaterData->calculate();
        }
        return $this;
    }
}
