<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Model\ResourceModel;

/**
 * SaveForLater item model resource
 */
class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('saveforlater_item', 'saveforlater_item_id');
    }

    /**
     * Load item by SaveForLater, product and shared stores
     *
     * @param \Mdbhojwani\SaveForLater\Model\Item $object
     * @param int $saveForLaterId
     * @param int $productId
     * @param array $sharedStores
     * @return $this
     */
    public function loadByProductSaveForLater($object, $saveForLaterId, $productId, $sharedStores)
    {
        $connection = $this->getConnection();
        $storeWhere = $connection->quoteInto('store_id IN (?)', $sharedStores);
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'saveforlater_id=:saveforlater_id AND ' . 'product_id=:product_id AND ' . $storeWhere
        );
        $bind = ['saveforlater_id' => $saveForLaterId, 'product_id' => $productId];
        $data = $connection->fetchRow($select, $bind);
        if ($data) {
            $object->setData($data);
        }
        $this->_afterLoad($object);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $hasDataChanges = $object->hasDataChanges();
        $object->setIsOptionsSaved(false);

        $result = parent::save($object);

        if ($hasDataChanges && !$object->isOptionsSaved()) {
            $object->saveItemOptions();
        }
        return $result;
    }
}
