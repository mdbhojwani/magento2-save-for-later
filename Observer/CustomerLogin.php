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

/**
 * Class CustomerLogin
 */
class CustomerLogin implements ObserverInterface
{
    /**
     * @var \Mdbhojwani\SaveForLater\Helper\Data
     */
    protected $saveForLaterData;

    /**
     * @param Data $saveForLaterData
     */
    public function __construct(Data $saveForLaterData)
    {
        $this->saveForLaterData = $saveForLaterData;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->saveForLaterData->calculate();
    }
}
