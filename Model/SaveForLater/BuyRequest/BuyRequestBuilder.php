<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Model\SaveForLater\BuyRequest;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Mdbhojwani\SaveForLater\Model\SaveForLater\Data\SaveForLaterItem;

/**
 * Building buy request for all product types
 */
class BuyRequestBuilder
{
    /**
     * @var BuyRequestDataProviderInterface[]
     */
    private $providers;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param array $providers
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        array $providers = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->providers = $providers;
    }

    /**
     * Build product buy request for adding to SaveForLater
     *
     * @param SaveForLaterItem $saveForLaterItemData
     * @param int|null $productId
     *
     * @return DataObject
     */
    public function build(SaveForLaterItem $saveForLaterItemData, ?int $productId = null): DataObject
    {
        $requestData = [
            [
                'qty' => $saveForLaterItemData->getQuantity(),
            ]
        ];

        foreach ($this->providers as $provider) {
            $requestData[] = $provider->execute($saveForLaterItemData, $productId);
        }

        return $this->dataObjectFactory->create(['data' => array_merge(...$requestData)]);
    }
}
