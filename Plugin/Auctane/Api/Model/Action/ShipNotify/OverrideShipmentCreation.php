<?php
declare(strict_types=1);

namespace RadWorks\ShipStationShipNotify\Plugin\Auctane\Api\Model\Action\ShipNotify;

use Auctane\Api\Model\Action\ShipNotify as Subject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\ShipmentTrackInterface;
use Magento\Sales\Model\Order;
use RadWorks\QuickOrderShipment\Api\OrderShipmentBuilderInterface;
use RadWorks\ShipStationShipNotify\Model\ConfigInterface;
use SimpleXMLElement as XML;

class OverrideShipmentCreation
{
    /**
     * @var ConfigInterface $config
     */
    private ConfigInterface $config;

    /**
     * @var OrderShipmentBuilderInterface $shipmentBuilder
     */
    private OrderShipmentBuilderInterface $shipmentBuilder;

    /**
     * @param ConfigInterface $config
     * @param OrderShipmentBuilderInterface $shipmentManagement
     */
    public function __construct(ConfigInterface $config, OrderShipmentBuilderInterface $shipmentManagement)
    {
        $this->config = $config;
        $this->shipmentBuilder = $shipmentManagement;
    }

    /**
     * @param Subject $shipNotify
     * @param callable $proceed
     * @param Order $order
     * @param array $qtys
     * @param XML $xml
     * @return Subject
     * @throws LocalizedException
     */
    public function aroundGetOrderShipment(
        Subject  $shipNotify,
        callable $proceed,
        Order    $order,
        array    $qtys,
        XML      $xml
    ): Subject
    {
        if (!$this->config->isShipmentImportEnabled()) {
            return $proceed($order, $qtys, $xml);
        }

        $track = [
            ShipmentTrackInterface::TRACK_NUMBER => (string)$xml->TrackingNumber,
            ShipmentTrackInterface::TITLE => strtolower((string)$xml->Carrier),
            ShipmentTrackInterface::CARRIER_CODE => strtoupper((string)$xml->Carrier)
        ];

        $this->shipmentBuilder
            ->skipInventoryDeduction($this->config->isShipmentInventoryValidationEnabled())
            ->build($order, $this->getQuantities($order, $qtys))
            ->addTrack($track)
            ->addComment((string)$xml->InternalNotes)
            ->save();

        return $shipNotify;
    }

    /**
     * Get quantities in the required format
     *
     * @param array $qtys
     * @param Order $order
     * @return array
     */
    public function getQuantities(Order $order, array $qtys): array
    {
        $quantities = [];
        foreach ($qtys as $orderItemId => $qty) {
            $quantities[$order->getItemById($orderItemId)->getSku()] = (float)$qty;
        }

        return $quantities;
    }
}