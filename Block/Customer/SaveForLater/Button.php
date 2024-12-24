<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block\Customer\SaveForLater;

/**
 * SaveForLater block customer item cart column
 */
class Button extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Mdbhojwani\SaveForLater\Model\Config
     */
    protected $saveForLaterConfig;

    /**
     * @var \Mdbhojwani\SaveForLater\Helper\Data
     */
    protected $saveForLaterData = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mdbhojwani\SaveForLater\Helper\Data $saveForLaterData
     * @param \Mdbhojwani\SaveForLater\Model\Config $saveForLaterConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mdbhojwani\SaveForLater\Helper\Data $saveForLaterData,
        \Mdbhojwani\SaveForLater\Model\Config $saveForLaterConfig,
        array $data = []
    ) {
        $this->saveForLaterData = $saveForLaterData;
        $this->saveForLaterConfig = $saveForLaterConfig;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current SaveForLater
     *
     * @return \Mdbhojwani\SaveForLater\Model\SaveForLater
     */
    public function getSaveForLater()
    {
        return $this->saveForLaterData->getSaveForLater();
    }

    /**
     * Retrieve SaveForLater config
     *
     * @return \Mdbhojwani\SaveForLater\Model\Config
     */
    public function getConfig()
    {
        return $this->saveForLaterConfig;
    }
}
