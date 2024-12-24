<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Model\SaveForLater;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Exception\LocalizedException;
use Mdbhojwani\SaveForLater\Model\Item as SaveForLaterItem;
use Mdbhojwani\SaveForLater\Model\ItemFactory as SaveForLaterItemFactory;
use Mdbhojwani\SaveForLater\Model\ResourceModel\Item as SaveForLaterItemResource;
use Mdbhojwani\SaveForLater\Model\SaveForLater;
use Mdbhojwani\SaveForLater\Model\SaveForLater\BuyRequest\BuyRequestBuilder;
use Mdbhojwani\SaveForLater\Model\SaveForLater\Data\SaveForLaterItem as SaveForLaterItemData;
use Mdbhojwani\SaveForLater\Model\SaveForLater\Data\SaveForLaterOutput;

/**
 * Updating product items in SaveForLater
 */
class UpdateProductsInSaveForLater
{
    /**#@+
     * Error message codes
     */
    private const ERROR_UNDEFINED = 'UNDEFINED';
    /**#@-*/

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var BuyRequestBuilder
     */
    private $buyRequestBuilder;

    /**
     * @var SaveForLaterItemFactory
     */
    private $saveForLaterItemFactory;

    /**
     * @var SaveForLaterItemResource
     */
    private $saveForLaterItemResource;

    /**
     * @param BuyRequestBuilder $buyRequestBuilder
     * @param SaveForLaterItemFactory $saveForLaterItemFactory
     * @param SaveForLaterItemResource $saveForLaterItemResource
     */
    public function __construct(
        BuyRequestBuilder $buyRequestBuilder,
        SaveForLaterItemFactory $saveForLaterItemFactory,
        SaveForLaterItemResource $saveForLaterItemResource
    ) {
        $this->buyRequestBuilder = $buyRequestBuilder;
        $this->saveForLaterItemFactory = $saveForLaterItemFactory;
        $this->saveForLaterItemResource = $saveForLaterItemResource;
    }

    /**
     * Adding products to SaveForLater
     *
     * @param SaveForLater $saveForLater
     * @param array $saveForLaterItems
     *
     * @return SaveForLaterOutput
     */
    public function execute(SaveForLater $saveForLater, array $saveForLaterItems): SaveForLaterOutput
    {
        foreach ($saveForLaterItems as $saveForLaterItem) {
            $this->updateItemInSaveForLater($saveForLater, $saveForLaterItem);
        }

        return $this->prepareOutput($saveForLater);
    }

    /**
     * Update product item in SaveForLater
     *
     * @param SaveForLater $saveForLater
     * @param SaveForLaterItemData $saveForLaterItemData
     *
     * @return void
     */
    private function updateItemInSaveForLater(SaveForLater $saveForLater, SaveForLaterItemData $saveForLaterItemData): void
    {
        try {
            if ($saveForLater->getItem($saveForLaterItemData->getId()) == null) {
                throw new LocalizedException(
                    __(
                        'The Save For Later item with ID "%id" does not belong to the Save For Later',
                        ['id' => $saveForLaterItemData->getId()]
                    )
                );
            }
            $saveForLater->getItemCollection()->clear();
            $options = $this->buyRequestBuilder->build($saveForLaterItemData);
            /** @var SaveForLaterItem $saveForLaterItem */
            $saveForLaterItem = $this->saveForLaterItemFactory->create();
            $this->saveForLaterItemResource->load($saveForLaterItem, $saveForLaterItemData->getId());
            $saveForLaterItem->setDescription($saveForLaterItemData->getDescription());
            if ((int)$saveForLaterItemData->getQuantity() === 0) {
                throw new LocalizedException(__("The quantity of a Save For Later item cannot be 0"));
            }
            if ($saveForLaterItem->getProduct()->getStatus() == Status::STATUS_DISABLED) {
                throw new LocalizedException(__("The product is disabled"));
            }
            $resultItem = $saveForLater->updateItem($saveForLaterItem, $options);

            if (is_string($resultItem)) {
                $this->addError($resultItem);
            }
        } catch (LocalizedException $exception) {
            $this->addError($exception->getMessage());
        }
    }

    /**
     * Add SaveForLater line item error
     *
     * @param string $message
     * @param string|null $code
     *
     * @return void
     */
    private function addError(string $message, string $code = null): void
    {
        $this->errors[] = new Data\Error(
            $message,
            $code ?? self::ERROR_UNDEFINED
        );
    }

    /**
     * Prepare output
     *
     * @param SaveForLater $saveForLater
     *
     * @return SaveForLaterOutput
     */
    private function prepareOutput(SaveForLater $saveForLater): SaveForLaterOutput
    {
        $output = new SaveForLaterOutput($saveForLater, $this->errors);
        $this->errors = [];

        return $output;
    }
}
