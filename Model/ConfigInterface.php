<?php
declare(strict_types=1);

namespace RadWorks\ShipStationShipNotify\Model;

/**
 * Module's config model
 */
interface ConfigInterface
{
    /**
     * Check if import enabled
     *
     * @return bool
     */
    public function isShipmentImportEnabled(): bool;

    /**
     * Get flag that disables/enables inventory validation
     *
     * @return bool
     */
    public function isShipmentInventoryValidationEnabled(): bool;
}