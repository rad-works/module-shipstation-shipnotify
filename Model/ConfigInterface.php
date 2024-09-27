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
     * Get import data source entity
     *
     * @return int
     */
    public function getShipmentImportSource(): int;

    /**
     * Get flag that disables/enables inventory validation
     *
     * @return bool
     */
    public function isShipmentInventoryValidationEnabled(): bool;
}