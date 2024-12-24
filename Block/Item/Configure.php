<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block\Item;

/**
 * SaveForLater Item Configure block
 */
class Configure extends \Magento\Framework\View\Element\Template
{
    /**
     * SaveForLater data
     *
     * @var \Mdbhojwani\SaveForLater\Helper\Data
     */
    protected $saveForLaterData = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mdbhojwani\SaveForLater\Helper\Data $saveForLaterData
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mdbhojwani\SaveForLater\Helper\Data $saveForLaterData,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->saveForLaterData = $saveForLaterData;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Return saveForLater widget options
     *
     * @return array
     */
    public function getSaveForLaterOptions()
    {
        return ['productType' => $this->escapeHtml($this->getProduct()->getTypeId())];
    }

    /**
     * Returns product being edited
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        return $this->_coreRegistry->registry('product');
    }

    /**
     * Get update params for http post
     *
     * @return bool|string
     */
    public function getUpdateParams()
    {
        return $this->saveForLaterData->getUpdateParams($this->getSaveForLaterItem());
    }

    /**
     * Returns SaveForLater item being configured
     *
     * @return \Magento\Catalog\Model\Product|\Mdbhojwani\SaveForLater\Model\Item
     */
    protected function getSaveForLaterItem()
    {
        return $this->_coreRegistry->registry('saveforlater_item');
    }

    /**
     * Configure product view blocks
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        // Set custom add to cart url
        $block = $this->getLayout()->getBlock('product.info');
        if ($block && $this->getSaveForLaterItem()) {
            $url = $this->saveForLaterData->getAddToCartUrl($this->getSaveForLaterItem());
            $block->setCustomAddToCartUrl($url);
        }

        return parent::_prepareLayout();
    }
}
