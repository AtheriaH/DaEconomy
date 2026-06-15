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
        $this->balances = $this->database->getAll();

        $this->getServer()->getCommandMap()->registerAll("daeconomy", [
            new MoneyCommand($this),
            new AddMoneyCommand($this),
            new RemoveMoneyCommand($this)
        ]);

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
