<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block\Customer\SaveForLater\Item\Column;

use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Mdbhojwani\SaveForLater\Helper\Data as SaveForLaterHelper;

/**
 * SaveForLater block customer item cart column
 */
class Image extends \Mdbhojwani\SaveForLater\Block\Customer\SaveForLater\Item\Column
{
    /** @var ItemResolverInterface */
    private $itemResolver;

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
        ConfigInterface $config = null,
        UrlBuilder $urlBuilder = null,
        ItemResolverInterface $itemResolver = null
    ) {
        $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(ItemResolverInterface::class);
        parent::__construct(
            $context,
            $httpContext,
            $saveForLaterHelper,
            $data,
            $config,
            $urlBuilder
        );
    }

    /**
     * Identify the product from which thumbnail should be taken.
     *
     * @return \Magento\Catalog\Model\Product
     * @since 101.0.5
     */
    public function getProductForThumbnail(\Mdbhojwani\SaveForLater\Model\Item $item) : \Magento\Catalog\Model\Product
    {
        return $this->itemResolver->getFinalProduct($item);
    }
}
