<?php
/**
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block\Customer\SaveForLater\Item\Column;

use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\ConfigInterface;
use Mdbhojwani\SaveForLater\Helper\Data as SaveForLaterHelper;

/**
 * SaveForLater block customer item cart column
 */
class Cart extends \Mdbhojwani\SaveForLater\Block\Customer\SaveForLater\Item\Column
{
    /**
     * @var View
     */
    private $productView;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param SaveForLaterHelper $saveForLaterHelper
     * @param array $data
     * @param ConfigInterface|null $config
     * @param UrlBuilder|null $urlBuilder
     * @param View|null $productView
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        SaveForLaterHelper $saveForLaterHelper,
        array $data = [],
        ?ConfigInterface $config = null,
        ?UrlBuilder $urlBuilder = null,
        ?View $productView = null
    ) {
        $this->productView = $productView ?: ObjectManager::getInstance()->get(View::class);
        parent::__construct($context, $httpContext, $saveForLaterHelper, $data, $config, $urlBuilder);
    }

    /**
     * Returns qty to show visually to user
     *
     * @param \Mdbhojwani\SaveForLater\Model\Item $item
     * @return float
     */
    public function getAddToCartQty(\Mdbhojwani\SaveForLater\Model\Item $item)
    {
        $qty = $item->getQty();
        $qty = $qty < $this->productView->getProductDefaultQty($this->getProductItem())
                ? $this->productView->getProductDefaultQty($this->getProductItem()) : $qty;
        return $qty ?: 1;
    }

    /**
     * Return product for current item
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductItem()
    {
        return $this->getItem()->getProduct();
    }
}
