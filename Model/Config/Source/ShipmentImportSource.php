<?php
declare(strict_types=1);

namespace RadWorks\ShipStationShipNotify\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use RadWorks\ShipStationShipNotify\Model\Config;

/**
 * Get available ShipStation API shipment sources
 */
class ShipmentImportSource implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => Config::SHIPMENT_SOURCE_TYPE_SHIPMENTS, 'label' => __('Shipments')],
            ['value' => Config::SHIPMENT_SOURCE_TYPE_FULFILLMENTS, 'label' => __('Fulfillments')],
            ['value' => Config::SHIPMENT_SOURCE_TYPE_ALL, 'label' => __('All')]
        ];
    }
}