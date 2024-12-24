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

use Magento\Framework\Exception\LocalizedException;
use Mdbhojwani\SaveForLater\Model\Item as SaveForLaterItem;
use Mdbhojwani\SaveForLater\Model\ItemFactory as SaveForLaterItemFactory;
use Mdbhojwani\SaveForLater\Model\ResourceModel\Item as SaveForLaterItemResource;
use Mdbhojwani\SaveForLater\Model\SaveForLater;
use Mdbhojwani\SaveForLater\Model\SaveForLater\Data\SaveForLaterOutput;

/**
 * Remove product items from SaveForLater
 */
class RemoveProductsFromSaveForLater
{
    /**#@+
     * Error message codes
     */
    private const ERROR_PRODUCT_NOT_FOUND = 'PRODUCT_NOT_FOUND';
    private const ERROR_UNDEFINED = 'UNDEFINED';
    /**#@-*/

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var SaveForLaterItemFactory
     */
    private $saveForLaterItemFactory;

    /**
     * @var SaveForLaterItemResource
     */
    private $saveForLaterItemResource;

    /**
     * @param SaveForLaterItemFactory $saveForLaterItemFactory
     * @param SaveForLaterItemResource $saveForLaterItemResource
     */
    public function __construct(
        SaveForLaterItemFactory $saveForLaterItemFactory,
        SaveForLaterItemResource $saveForLaterItemResource
    ) {
        $this->saveForLaterItemFactory = $saveForLaterItemFactory;
        $this->saveForLaterItemResource = $saveForLaterItemResource;
    }

    /**
     * Removing items from SaveForLater
     *
     * @param SaveForLater $saveForLater
     * @param array $saveForLaterItemsIds
     *
     * @return SaveForLaterOutput
     */
    public function execute(SaveForLater $saveForLater, array $saveForLaterItemsIds): SaveForLaterOutput
    {
        foreach ($saveForLaterItemsIds as $saveForLaterItemId) {
            $this->removeItemFromSaveForLater((int) $saveForLaterItemId, $saveForLater);
        }

        return $this->prepareOutput($saveForLater);
    }

    /**
     * Remove product item from SaveForLater
     *
     * @param int $saveForLaterItemId
     * @param SaveForLater $saveForLater
     *
     * @return void
     */
    private function removeItemFromSaveForLater(int $saveForLaterItemId, SaveForLater $saveForLater): void
    {
        try {
            if ($saveForLater->getItem($saveForLaterItemId) == null) {
                throw new LocalizedException(
                    __(
                        'The Save For Later item with ID "%id" does not belong to the Save For Later',
                        ['id' => $saveForLaterItemId]
                    )
                );
            }
            $saveForLater->getItemCollection()->clear();
            /** @var SaveForLaterItem $saveForLaterItem */
            $saveForLaterItem = $this->saveForLaterItemFactory->create();
            $this->saveForLaterItemResource->load($saveForLaterItem, $saveForLaterItemId);
            if (!$saveForLaterItem->getId()) {
                $this->addError(
                    __('Could not find a Save For Later item with ID "%id"', ['id' => $saveForLaterItemId])->render(),
                    self::ERROR_PRODUCT_NOT_FOUND
                );
            }

            $this->saveForLaterItemResource->delete($saveForLaterItem);
        } catch (LocalizedException $exception) {
            $this->addError($exception->getMessage());
        } catch (\Exception $e) {
            $this->addError(
                __(
                    'We can\'t delete the item with ID "%id" from the Save For Later right now.',
                    ['id' => $saveForLaterItemId]
                )->render()
            );
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
