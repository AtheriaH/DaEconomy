<?php

declare(strict_types=1);

namespace DaEconomy\provider;

use pocketmine\utils\Config;

class YamlProvider implements Provider {

    private Config $database;
    /** @var array<string, int> */
    private array $balances = [];

    // We pass the folder path so the provider knows exactly where to save the file
    public function __construct(private string $dataFolder) {}

    public function open(): void {
        // PM5 natively detects it's a YAML file from the extension
        $this->database = new Config($this->dataFolder . "players.yml");
        $this->balances = $this->database->getAll();
    }

    public function accountExists(string $xuid): bool {
        return isset($this->balances[$xuid]);
    }

    public function createAccount(string $xuid, int $defaultMoney = 1000): bool {
        if ($this->accountExists($xuid)) {
            return false; // Account already exists
        }
        $this->balances[$xuid] = $defaultMoney;
        return true;
    }

    public function removeAccount(string $xuid): bool {
        if (!$this->accountExists($xuid)) {
            return false;
        }
        unset($this->balances[$xuid]);
        return true;
    }

    public function getMoney(string $xuid): int|bool {
        return $this->balances[$xuid] ?? false;
    }

    public function setMoney(string $xuid, int $amount): bool {
        if (!$this->accountExists($xuid)) {
            return false;
        }
        // Prevents the balance from ever going below zero
        $this->balances[$xuid] = max(0, $amount);
        return true;
    }

    public function addMoney(string $xuid, int $amount): bool {
        if (!$this->accountExists($xuid) || $amount <= 0) {
            return false;
        }
        $this->balances[$xuid] += $amount;
        return true;
    }

    public function reduceMoney(string $xuid, int $amount): bool {
        if (!$this->accountExists($xuid) || $amount <= 0) {
            return false;
        }
        // Prevent going into debt
        if ($this->balances[$xuid] < $amount) {
            return false; 
        }
        $this->balances[$xuid] -= $amount;
        return true;
    }

    public function getAll(): array {
        return $this->balances;
    }

    public function getName(): string {
        return "YAML";
    }

    public function save(): void {
        $this->database->setAll($this->balances);
        $this->database->save();
    }

    public function close(): void {
        $this->save();
    }
}
