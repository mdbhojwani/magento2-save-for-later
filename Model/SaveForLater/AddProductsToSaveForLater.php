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

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mdbhojwani\SaveForLater\Model\ResourceModel\SaveForLater as SaveForLaterResourceModel;
use Mdbhojwani\SaveForLater\Model\SaveForLater;
use Mdbhojwani\SaveForLater\Model\SaveForLater\BuyRequest\BuyRequestBuilder;
use Mdbhojwani\SaveForLater\Model\SaveForLater\Data\SaveForLaterItem;
use Mdbhojwani\SaveForLater\Model\SaveForLater\Data\SaveForLaterOutput;

/**
 * Adding products to SaveForLater
 */
class AddProductsToSaveForLater
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
     * @var BuyRequestBuilder
     */
    private $buyRequestBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SaveForLaterResourceModel
     */
    private $saveForLaterResource;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param BuyRequestBuilder $buyRequestBuilder
     * @param SaveForLaterResourceModel $saveForLaterResource
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        BuyRequestBuilder $buyRequestBuilder,
        SaveForLaterResourceModel $saveForLaterResource
    ) {
        $this->productRepository = $productRepository;
        $this->buyRequestBuilder = $buyRequestBuilder;
        $this->saveForLaterResource = $saveForLaterResource;
    }

    /**
     * Adding products to SaveForLater
     *
     * @param SaveForLater $saveForLater
     * @param array $saveForLaterItems
     *
     * @return SaveForLaterOutput
     *
     * @throws AlreadyExistsException
     */
    public function execute(SaveForLater $saveForLater, array $saveForLaterItems): SaveForLaterOutput
    {
        foreach ($saveForLaterItems as $saveForLaterItem) {
            $this->addItemToSaveForLater($saveForLater, $saveForLaterItem);
        }

        $saveForLaterOutput = $this->prepareOutput($saveForLater);

        if ($saveForLater->isObjectNew() || count($saveForLaterOutput->getErrors()) !== count($saveForLaterItems)) {
            $this->saveForLaterResource->save($saveForLater);
        }

        return $saveForLaterOutput;
    }

    /**
     * Add product item to SaveForLater
     *
     * @param SaveForLater $saveForLater
     * @param SaveForLaterItem $saveForLaterItem
     *
     * @return void
     */
    private function addItemToSaveForLater(SaveForLater $saveForLater, SaveForLaterItem $saveForLaterItem): void
    {
        $sku = $saveForLaterItem->getParentSku() ?? $saveForLaterItem->getSku();

        try {
            $product = $this->productRepository->get($sku, false, null, true);
        } catch (NoSuchEntityException $e) {
            $this->addError(
                __('Could not find a product with SKU "%sku"', ['sku' => $sku])->render(),
                self::ERROR_PRODUCT_NOT_FOUND
            );

            return;
        }

        try {
            if ((int)$saveForLaterItem->getQuantity() === 0) {
                throw new LocalizedException(__("The quantity of a Save For Later item cannot be 0"));
            }
            $options = $this->buyRequestBuilder->build($saveForLaterItem, (int) $product->getId());
            $result = $saveForLater->addNewItem($product, $options, true);

            if (is_string($result)) {
                $this->addError($result);
            }
        } catch (LocalizedException $exception) {
            $this->addError($exception->getMessage());
        } catch (\Throwable $e) {
            $this->addError(
                __(
                    'Could not add the product with SKU "%sku" to the Save For Later:: %message',
                    ['sku' => $sku, 'message' => $e->getMessage()]
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
