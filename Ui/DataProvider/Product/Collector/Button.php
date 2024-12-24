<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Ui\DataProvider\Product\Collector;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRender\ButtonInterfaceFactory;
use Magento\Catalog\Api\Data\ProductRenderInfoDtoInterface;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Model\ProductRenderInfoDto;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderInfoProviderInterface;
use Mdbhojwani\SaveForLater\Helper\Data;

/**
 * Collect information needed to render SaveForLater button on front
 */
class Button implements ProductRenderCollectorInterface
{
    /** Url Key */
    const KEY_SAVEFORLATER_URL_PARAMS = "saveforlater_url_params";

    /**
     * @var Data
     */
    private $saveForLaterHelper;

    /**
     * @var \Magento\Catalog\Api\Data\ProductRender\ProductRenderExtensionInterfaceFactory
     */
    private $productRenderExtensionFactory;

    /**
     * @var ButtonInterfaceFactory
     */
    private $buttonInterfaceFactory;

    /**
     * @param Data $saveForLaterHelper
     * @param \Magento\Catalog\Api\Data\ProductRenderExtensionFactory $productRenderExtensionFactory
     * @param ButtonInterfaceFactory $buttonInterfaceFactory
     */
    public function __construct(
        Data $saveForLaterHelper,
        \Magento\Catalog\Api\Data\ProductRenderExtensionFactory $productRenderExtensionFactory,
        ButtonInterfaceFactory $buttonInterfaceFactory
    ) {
        $this->saveForLaterHelper = $saveForLaterHelper;
        $this->productRenderExtensionFactory = $productRenderExtensionFactory;
        $this->buttonInterfaceFactory = $buttonInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        /** @var \Magento\Catalog\Api\Data\ProductRenderExtensionInterface $extensionAttributes */
        $extensionAttributes = $productRender->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->productRenderExtensionFactory->create();
        }

        $button = $this->buttonInterfaceFactory->create();
        $button->setUrl($this->saveForLaterHelper->getAddParams($product));
        $extensionAttributes->setSaveForLaterButton($button);
        $productRender->setExtensionAttributes($extensionAttributes);
    }
}
