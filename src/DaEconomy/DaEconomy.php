<?php

declare(strict_types=1);

namespace DaEconomy;

use DaEconomy\command\AddMoneyCommand;
use DaEconomy\command\MoneyCommand;
use DaEconomy\command\RemoveMoneyCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;

class DaEconomy extends PluginBase {

    private Config $database;
    private array $balances = [];

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $this->database = new Config($this->getDataFolder() . "players.yml", Config::YAML);
        
        // FIX: Crucial array_change_key_case so capitalized names don't lose their data
        $this->balances = array_change_key_case($this->database->getAll(), CASE_LOWER);

        $this->getServer()->getCommandMap()->registerAll("daeconomy", [
            new MoneyCommand($this),
            new AddMoneyCommand($this),
            new RemoveMoneyCommand($this)
        ]);

        // Saves data every 5 minutes in the background without causing lag
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (): void {
            $this->saveDatabase();
        }), 20 * 60 * 5);
    }

    protected function onDisable(): void {
        $this->saveDatabase();
    }

    private function saveDatabase(): void {
        $this->database->setAll($this->balances);
        $this->database->save();
    }

    public function getBalance(string $player): int {
        return $this->balances[strtolower($player)] ?? (int) $this->getConfig()->get("starting-money", 1000);
    }

    public function setBalance(string $player, int $amount): void {
        $this->balances[strtolower($player)] = max(0, $amount);
    }

    public function addBalance(string $player, int $amount): void {
        if ($amount > 0) {
            $this->setBalance($player, $this->getBalance($player) + $amount);
        }
    }

    public function removeBalance(string $player, int $amount): void {
        if ($amount > 0) {
            $this->setBalance($player, $this->getBalance($player) - $amount);
        }
    }

    public function formatMoney(int $amount): string {
        return $this->getConfig()->get("currency-symbol", "$") . number_format($amount);
    }
}
