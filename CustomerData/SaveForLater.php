<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\CustomerData;

use Magento\Catalog\Model\Product\Image\NotLoadInfoImageException;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\App\ObjectManager;

/**
 * SaveForLater section
 */
class SaveForLater implements SectionSourceInterface
{
    /**
     * @var string
     */
    const SIDEBAR_ITEMS_NUMBER = 3;

    /**
     * @var \Mdbhojwani\SaveForLater\Helper\Data
     */
    protected $saveForLaterHelper;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @var \Mdbhojwani\SaveForLater\Block\Customer\Sidebar
     */
    protected $block;

    /**
     * @var \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface
     */
    private $itemResolver;

    /**
     * @param \Mdbhojwani\SaveForLater\Helper\Data $saveForLaterHelper
     * @param \Mdbhojwani\SaveForLater\Block\Customer\Sidebar $block
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     * @param \Magento\Framework\App\ViewInterface $view
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface|null $itemResolver
     */
    public function __construct(
        \Mdbhojwani\SaveForLater\Helper\Data $saveForLaterHelper,
        \Mdbhojwani\SaveForLater\Block\Customer\Sidebar $block,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Framework\App\ViewInterface $view,
        \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface $itemResolver = null
    ) {
        $this->saveForLaterHelper = $saveForLaterHelper;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->block = $block;
        $this->view = $view;
        $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface::class
        );
    }

    /**
     * @inheritdoc
     */
    public function getSectionData()
    {
        $counter = $this->getCounter();
        return [
            'counter' => $counter,
            'items' => $counter ? $this->getItems() : [],
        ];
    }

    /**
     * Get counter
     *
     * @return string
     */
    protected function getCounter()
    {
        return $this->createCounter($this->saveForLaterHelper->getItemCount());
    }

    /**
     * Create button label based on SaveForLater item quantity
     *
     * @param int $count
     * @return \Magento\Framework\Phrase|null
     */
    protected function createCounter($count)
    {
        if ($count > 1) {
            return __('%1 items', $count);
        } elseif ($count == 1) {
            return __('1 item');
        }
        return null;
    }

    /**
     * Get SaveForLater items
     *
     * @return array
     */
    protected function getItems()
    {
        $collection = $this->saveForLaterHelper->getSaveForLaterItemCollection();
        $collection->clear()->setPageSize(self::SIDEBAR_ITEMS_NUMBER)
            ->setInStockFilter(true)->setOrder('added_at');

        $items = [];
        foreach ($collection as $saveForLaterItem) {
            $items[] = $this->getItemData($saveForLaterItem);
        }
        return $items;
    }

    /**
     * Retrieve SaveForLater item data
     *
     * @param \Mdbhojwani\SaveForLater\Model\Item $saveForLaterItem
     * @return array
     */
    protected function getItemData(\Mdbhojwani\SaveForLater\Model\Item $saveForLaterItem)
    {
        $product = $saveForLaterItem->getProduct();
        return [
            'image' => $this->getImageData($this->itemResolver->getFinalProduct($saveForLaterItem)),
            'product_sku' => $product->getSku(),
            'product_id' => $product->getId(),
            'product_url' => $this->saveForLaterHelper->getProductUrl($saveForLaterItem),
            'product_name' => $product->getName(),
            'product_price' => $this->block->getProductPriceHtml(
                $product,
                'saveforlater_configured_price',
                \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                ['item' => $saveForLaterItem]
            ),
            'product_is_saleable_and_visible' => $product->isSaleable() && $product->isVisibleInSiteVisibility(),
            'product_has_required_options' => $product->getTypeInstance()->hasRequiredOptions($product),
            'add_to_cart_params' => $this->saveForLaterHelper->getAddToCartParams($saveForLaterItem),
            'delete_item_params' => $this->saveForLaterHelper->getRemoveParams($saveForLaterItem),
        ];
    }

    /**
     * Retrieve product image data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getImageData($product)
    {
        /** @var \Magento\Catalog\Helper\Image $helper */
        $helper = $this->imageHelperFactory->create()
            ->init($product, 'saveforlater_sidebar_block');

        return [
            'template' => 'Magento_Catalog/product/image_with_borders',
            'src' => $helper->getUrl(),
            'width' => $helper->getWidth(),
            'height' => $helper->getHeight(),
            'alt' => $helper->getLabel(),
        ];
    }
}
