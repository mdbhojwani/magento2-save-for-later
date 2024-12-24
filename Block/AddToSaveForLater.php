<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block;

use Magento\Catalog\Api\Data\ProductTypeInterface;
use Magento\Catalog\Api\ProductTypeListInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * SaveForLater js plugin initialization block
 */
class AddToSaveForLater extends Template
{
    /**
     * Product types
     *
     * @var array|null
     */
    private $productTypes;

    /**
     * @var ProductTypeListInterface
     */
    private $productTypeList;

    /**
     * AddToSaveForLater constructor.
     *
     * @param Context $context
     * @param array $data
     * @param ProductTypeListInterface|null $productTypeList
     */
    public function __construct(
        Context $context,
        array $data = [],
        ?ProductTypeListInterface $productTypeList = null
    ) {
        parent::__construct($context, $data);
        $this->productTypes = [];
        $this->productTypeList = $productTypeList ?: ObjectManager::getInstance()->get(ProductTypeListInterface::class);
    }

    /**
     * Returns SaveForLater widget options
     *
     * @return array
     * @since 100.1.0
     */
    public function getSaveForLaterOptions()
    {
        return ['productType' => $this->getProductTypes()];
    }

    /**
     * Returns an array of product types
     *
     * @return array
     */
    private function getProductTypes(): array
    {
        if (count($this->productTypes) === 0) {
            /** @var ProductTypeInterface productTypes */
            $this->productTypes = array_map(function ($productType) {
                return $productType->getName();
            }, $this->productTypeList->getProductTypes());
        }
        return $this->productTypes;
    }
}
