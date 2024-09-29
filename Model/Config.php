<?php
declare(strict_types=1);

namespace RadWorks\ShipStationShipNotify\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config implements ConfigInterface
{
    /**
     * XML config path
     */
    public const XML_PATH_OVERRIDE_SHIPMENT = 'radworks/shipstation_shipnotify/shipment_enabled';
    public const XML_PATH_OVERRIDE_SHIPMENT_SKIP_INVENTORY = 'radworks/shipstation_shipnotify/skip_inventory_validation';

    /**
     * @var ScopeConfigInterface $scopeConfig
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check if import enabled
     *
     * @return bool
     */
    public function isShipmentImportEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_OVERRIDE_SHIPMENT);
    }

    /**
     * Get flag that disables/enables inventory validation
     *
     * @return bool
     */
    public function isShipmentInventoryValidationEnabled(): bool
    {
        return !$this->scopeConfig->isSetFlag(self::XML_PATH_OVERRIDE_SHIPMENT_SKIP_INVENTORY);
    }
}