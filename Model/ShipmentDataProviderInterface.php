<?php
declare(strict_types=1);

namespace RadWorks\ShipStationShipNotify\Model;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Get data from ShipStation API to create shipments
 */
interface ShipmentDataProviderInterface
{
    /**
     * Get data by order and source type
     *
     * @param OrderInterface $order
     * @param string $sourceType
     * @return array
     */
    public function get(OrderInterface $order, string $sourceType): array;
}