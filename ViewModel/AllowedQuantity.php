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

use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\CatalogInventory\Model\StockRegistry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ViewModel for SaveForLater Cart Block
 */
class AllowedQuantity implements ArgumentInterface
{
    /**
     * @var StockRegistry
     */
    private $stockRegistry;

    /**
     * @var ItemInterface
     */
    private $item;

    /**
     * @param StockRegistry $stockRegistry
     */
    public function __construct(StockRegistry $stockRegistry)
    {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Set product configuration item
     *
     * @param ItemInterface $item
     * @return self
     */
    public function setItem(ItemInterface $item): self
    {
        $this->item = $item;
        return $this;
    }

    /**
     * Get product configuration item
     *
     * @return ItemInterface
     */
    public function getItem(): ItemInterface
    {
        return $this->item;
    }

    /**
     * Get min and max qty for SaveForLater form.
     *
     * @return array
     */
    public function getMinMaxQty(): array
    {
        $product = $this->getItem()->getProduct();
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $params = [];

        $params['minAllowed'] = (float)$stockItem->getMinSaleQty();
        if ($stockItem->getMaxSaleQty()) {
            $params['maxAllowed'] = (float)$stockItem->getMaxSaleQty();
        } else {
            $params['maxAllowed'] = (float)StockDataFilter::MAX_QTY_VALUE;
        }

        return $params;
    }
}
