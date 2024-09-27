<?php
declare(strict_types=1);

namespace RadWorks\ShipStationShipNotify\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use RadWorks\ShipStationApiCore\Api\ClientInterface;
use RadWorks\ShipStationApiCore\Exception\InvalidResponse;
use RadWorks\ShipStationApiCore\Model\Http\Client;

/**
 * Get data from ShipStation API to create shipments
 */
class ShipmentDataProvider implements ShipmentDataProviderInterface
{

    private ClientInterface $apiClient;

    public function __construct(ClientInterface $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Get data by order and source type
     *
     * @param OrderInterface $order
     * @param string $sourceType
     * @return array
     * @throws InvalidResponse|LocalizedException
     */
    public function get(OrderInterface $order, string $sourceType): array
    {
        $result = [];
        $sourceTypeAny = $sourceType == Config::SHIPMENT_SOURCE_TYPE_ALL;
        if ($sourceTypeAny || $sourceType == Config::SHIPMENT_SOURCE_TYPE_SHIPMENTS) {
            $result[Config::SHIPMENT_SOURCE_TYPE_SHIPMENTS] = $this->getExternalShipments($order);
        }

        if ($sourceTypeAny || $sourceType == Config::SHIPMENT_SOURCE_TYPE_FULFILLMENTS) {
            $result[Config::SHIPMENT_SOURCE_TYPE_FULFILLMENTS] = $this->getExternalFulfillments($order);
        }

        return $this->processResult($result);
    }

    /**
     * Get all shipments by order
     *
     * @param OrderInterface $order
     * @return array
     * @throws InvalidResponse
     */
    private function getExternalShipments(OrderInterface $order): array
    {
        $params = [
            Client::API_PARAM_ORDER_URL => $order->getIncrementId(),
            Client::API_PARAM_ORDER_FULL => true
        ];
        $result = $this->fetchData(
            Client::API_SHIPMENTS_URL . '?' . http_build_query($params),
            Config::SHIPMENT_SOURCE_TYPE_SHIPMENTS
        );

        return array_column($result, null, Client::API_RESPONSE_SHIPMENT_ID);
    }

    /**
     * Get all fulfillmetns by order
     *
     * @param OrderInterface $order
     * @return array
     * @throws InvalidResponse|LocalizedException
     */
    private function getExternalFulfillments(OrderInterface $order): array
    {
        $params = http_build_query([Client::API_PARAM_ORDER_URL => $order->getIncrementId()]);
        $url = Client::API_FULFILLMENTS_URL . '?' . $params;
        if (!$result = $this->fetchData($url, Config::SHIPMENT_SOURCE_TYPE_FULFILLMENTS)) {
            return $result;
        }

        $url = Client::API_ORDERS_URL . '?' . $params;
        if (!$externalOrders = $this->fetchData($url, Config::SHIPMENT_SOURCE_TYPE_ORDERS)) {
            throw new LocalizedException(__('ShipStation API: Order #%s does not exist.', $order->getIncrementId()));
        }

        $result = array_column($result, null, Client::API_RESPONSE_FULFILLMENT_ID);
        $externalOrders = array_column($externalOrders, null, Client::API_RESPONSE_ORDER_ID);
        foreach ($result as $index => $fulfillment) {
            $externalOrderId = $fulfillment[Client::API_RESPONSE_ORDER_ID];
            if (!array_key_exists($externalOrderId, $externalOrders)) {
                unset($result[$index]);
                continue;
            }

            $result[$index][Config::SHIPMENT_SOURCE_TYPE_ORDERS] = $externalOrders[$externalOrderId];
        }

        return $result;
    }

    /**
     * Process final result
     *
     * @param OrderInterface $order
     * @param array $result
     * @return array
     */
    private function processResult(OrderInterface $order, array $result): array
    {
        if (!$order->getShipmentsCollection()) {
            return $result;
        }

        /** @var ShipmentInterface $shipment */
        foreach ($order->getShipmentsCollection() as $shipment) {
            $externalId = $shipment->getData(Config::SHIPMENT_SOURCE_FIELD_ID);
            $sourceType = $shipment->getData(Config::SHIPMENT_SOURCE_FIELD_TYPE);
            //Exclude data for already created shipments
            if ($result[$sourceType][$externalId] ?? null) {
                unset($result[$sourceType][$externalId]);
            }
        }

        return $result;
    }

    /**
     * Fetch data from the ShipStation API
     *
     * @param string $url
     * @param string $sourceType
     * @return array
     * @throws InvalidResponse
     */
    private function fetchData(string $url, string $sourceType): array
    {
        $result = $this->apiClient->send($url, 'GET');
        if (!array_key_exists($sourceType, $result)) {
            throw new InvalidResponse(__('ShipStation API: No "%s" in the response.', $sourceType));
        }

        return $result[$sourceType];
    }
}