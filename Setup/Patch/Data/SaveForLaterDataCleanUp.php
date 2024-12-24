<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Setup\Patch\Data;

use Magento\Framework\DB\Query\Generator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Clean Up Data Removes unused data
 */
class SaveForLaterDataCleanUp implements DataPatchInterface
{
    /**
     * Batch size for query
     */
    private const BATCH_SIZE = 1000;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var Generator
     */
    private $queryGenerator;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RemoveData constructor.
     * @param Json $json
     * @param Generator $queryGenerator
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param LoggerInterface $logger
     */
    public function __construct(
        Json $json,
        Generator $queryGenerator,
        ModuleDataSetupInterface $moduleDataSetup,
        LoggerInterface $logger
    ) {
        $this->json = $json;
        $this->queryGenerator = $queryGenerator;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        try {
            $this->cleanSaveForLaterItemOptionTable();
        } catch (\Throwable $e) {
            $this->logger->warning(
                'SaveForLater module SaveForLaterDataCleanUp patch experienced an error and could not be completed.'
                . ' Please submit a support ticket or email us at security@magento.com.'
            );

            return $this;
        }

        return $this;
    }

    /**
     * Remove login data from saveforlater_item_option table.
     *
     * @throws LocalizedException
     */
    private function cleanSaveForLaterItemOptionTable()
    {
        $tableName = $this->moduleDataSetup->getTable('saveforlater_item_option');
        $select = $this->moduleDataSetup
            ->getConnection()
            ->select()
            ->from(
                $tableName,
                ['option_id', 'value']
            )
            ->where(
                'value LIKE ?',
                '%login%'
            );
        $iterator = $this->queryGenerator->generate('option_id', $select, self::BATCH_SIZE);
        $rowErrorFlag = false;
        foreach ($iterator as $selectByRange) {
            $optionRows = $this->moduleDataSetup->getConnection()->fetchAll($selectByRange);
            foreach ($optionRows as $optionRow) {
                try {
                    $rowValue = $this->json->unserialize($optionRow['value']);
                    if (is_array($rowValue)
                        && array_key_exists('login', $rowValue)
                    ) {
                        unset($rowValue['login']);
                    }
                    $rowValue = $this->json->serialize($rowValue);
                    $this->moduleDataSetup->getConnection()->update(
                        $tableName,
                        ['value' => $rowValue],
                        ['option_id = ?' => $optionRow['option_id']]
                    );
                } catch (\Throwable $e) {
                    $rowErrorFlag = true;
                    continue;
                }
            }
        }
        if ($rowErrorFlag) {
            $this->logger->warning(
                'Data clean up could not be completed due to unexpected data format in the table "'
                . $tableName
                . '". Please submit a support ticket or email us at security@magento.com.'
            );
        }
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [
            ConvertSerializedData::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}
