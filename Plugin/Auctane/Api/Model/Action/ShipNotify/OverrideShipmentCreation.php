<?php
declare(strict_types=1);

namespace RadWorks\ShipStationShipNotify\Plugin\Auctane\Api\Model\Action\ShipNotify;

use Auctane\Api\Model\Action\ShipNotify as Subject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use RadWorks\ShipStationShipNotify\Model\ConfigInterface;

class OverrideShipmentCreation
{
    /**
     * @var ConfigInterface $config
     */
    private ConfigInterface $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param Subject $shipNotify
     * @param callable $proceed
     * @param Order $order
     * @param mixed ...$arguments
     * @return Subject
     */
    public function aroundGetOrderShipment(Subject $shipNotify, callable $proceed, Order $order, ...$arguments): Subject
    {
        if (!$this->config->isShipmentImportEnabled()) {
            return $proceed($order, ...$arguments);
        }

        return $shipNotify;
    }
}