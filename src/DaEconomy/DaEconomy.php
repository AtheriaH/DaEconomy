<?php

declare(strict_types=1);

namespace DaEconomy;

use DaEconomy\command\AddMoneyCommand;
use DaEconomy\command\MoneyCommand;
use DaEconomy\command\RemoveMoneyCommand;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class DaEconomy extends PluginBase {

    private static self $instance;
    private Config $database;

    protected function onEnable(): void {
        self::$instance = $this;
        $this->saveDefaultConfig();

        $this->database = new Config($this->getDataFolder() . "players.yml", Config::YAML);

        $this->getServer()->getCommandMap()->registerAll("daeconomy", [
            new MoneyCommand($this),
            new AddMoneyCommand($this),
            new RemoveMoneyCommand($this)
        ]);
    }

    public static function getInstance(): self {
        return self::$instance;
    }

    public function getBalance(string $player): int {
        return (int) $this->database->get(strtolower($player), (int) $this->getConfig()->get("starting-money", 1000));
    }

    public function setBalance(string $player, int $amount): void {
        $this->database->set(strtolower($player), max(0, $amount));
        $this->database->save();
    }

    public function addBalance(string $player, int $amount): void {
        if ($amount <= 0) return;
        $this->setBalance($player, $this->getBalance($player) + $amount);
    }

    public function removeBalance(string $player, int $amount): void {
        if ($amount <= 0) return;
        $this->setBalance($player, $this->getBalance($player) - $amount);
    }

    public function formatMoney(int $amount): string {
        return $this->getConfig()->get("currency-symbol", "$") . number_format($amount);
    }
}
