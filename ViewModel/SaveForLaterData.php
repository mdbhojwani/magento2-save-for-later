<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\ViewModel;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Mdbhojwani\SaveForLater\Helper\Data as HelperData;

/**
 * ViewModel for SaveForLater Sidebar Block
 */
class SaveForLaterData implements ArgumentInterface
{
    /**
     * Object manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param HelperData $helperData
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        HelperData $helperData
    ) {
        $this->objectManager = $objectManager;
        $this->helperData = $helperData ?: $this->objectManager->get(HelperData::class);
    }

    /**
     * Retrieve customer SaveForLater url
     *
     * @param int $saveForLaterId
     * @return string
     */
    public function getListUrl($saveForLaterId = null): string
    {
        return $this->helperData->getListUrl($saveForLaterId);
    }

    /**
     * Check is allow SaveForLater module
     *
     * @return bool
     */
    public function isAllow(): bool
    {
        return $this->helperData->isAllow();
    }
}
