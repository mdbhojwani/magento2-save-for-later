<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block\Share\Email;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\ConfigInterface;
use Mdbhojwani\SaveForLater\Model\Item;
use Mdbhojwani\SaveForLater\Helper\Data as SaveForLaterHelper;

/**
 * SaveForLater share items
 */
class Items extends \Mdbhojwani\SaveForLater\Block\AbstractBlock
{
    /**
     * @var ItemResolverInterface
     */
    private $itemResolver;

    /**
     * @var string
     */
    protected $_template = 'Mdbhojwani_SaveForLater::email/items.phtml';

    /**
     * Items constructor.
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param SaveForLaterHelper $saveForLaterHelper
     * @param array $data
     * @param ConfigInterface|null $config
     * @param UrlBuilder|null $urlBuilder
     * @param ItemResolverInterface|null $itemResolver
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        SaveForLaterHelper $saveForLaterHelper,
        array $data = [],
        ConfigInterface $config = null,
        UrlBuilder $urlBuilder = null,
        ItemResolverInterface $itemResolver = null
    ) {
        parent::__construct($context, $httpContext, $saveForLaterHelper, $data, $config, $urlBuilder);
        $this->itemResolver = $itemResolver ?? ObjectManager::getInstance()->get(ItemResolverInterface::class);
    }

    /**
     * Identify the product from which thumbnail should be taken.
     *
     * @param Item $item
     *
     * @return Product
     * @since 101.2.0
     */
    public function getProductForThumbnail(Item $item): Product
    {
        return $this->itemResolver->getFinalProduct($item);
    }

    /**
     * Retrieve Product View URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     *
     * @return string
     */
    public function getProductUrl($product, $additional = [])
    {
        $additional['_scope_to_url'] = true;
        return parent::getProductUrl($product, $additional);
    }

    /**
     * Retrieve URL for add product to shopping cart
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     *
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        $additional['nocookie'] = 1;
        $additional['_scope_to_url'] = true;
        return parent::getAddToCartUrl($product, $additional);
    }

    /**
     * Check whether SaveForLater item has description
     *
     * @param \Mdbhojwani\SaveForLater\Model\Item $item
     *
     * @return bool
     */
    public function hasDescription($item)
    {
        $hasDescription = parent::hasDescription($item);
        if ($hasDescription) {
            return $item->getDescription() !== $this->saveForLaterHelper->defaultCommentString();
        }
        return $hasDescription;
    }
}
