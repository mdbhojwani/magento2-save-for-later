<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Pricing\ConfiguredPrice;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Catalog\Pricing\Price\FinalPrice;

/**
 * Class Downloadable for SaveForLater
 */
class Downloadable extends FinalPrice implements ConfiguredPriceInterface
{
    /**
     * Price type configured
     */
    const PRICE_CODE = 'configured_price';

    /**
     * @var ItemInterface
     */
    private $item;

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return max(0, parent::getValue() + $this->getLinkPrice());
    }

    /**
     * Retrieve calculated links price
     *
     * @return int
     */
    private function getLinkPrice()
    {
        $result = 0;
        if ($this->getProduct()->getLinksPurchasedSeparately()) {
            /** @var \Mdbhojwani\SaveForLater\Model\Item\Option $customOption */
            $customOption = $this->getProduct()->getCustomOption('downloadable_link_ids');
            if ($customOption) {
                $links = $this->getLinks();
                $linkIds = explode(',', (string) $customOption->getValue());
                foreach ($linkIds as $linkId) {
                    if (isset($links[$linkId])) {
                        $result += $links[$linkId]->getPrice();
                    }
                }
            }
        }
        return $result;
    }

    /**
     * This method get downloadable product links.
     *
     * @return \Magento\Downloadable\Model\Link[]
     */
    private function getLinks()
    {
        /** @var \Magento\Downloadable\Model\Product\Type $productType */
        $productType = $this->getProduct()->getTypeInstance();
        $links = $productType->getLinks($this->getProduct());
        return $links;
    }

    /**
     * @inheritdoc
     */
    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
        return $this;
    }
}
