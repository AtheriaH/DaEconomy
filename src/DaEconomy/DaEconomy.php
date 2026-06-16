<?php

declare(strict_types=1);

namespace DaEconomy;

use DaEconomy\provider\Provider;
use DaEconomy\provider\YamlProvider;
use DaEconomy\provider\MySQLProvider;
use DaEconomy\listener\PlayerListener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\Config;

class DaEconomy extends PluginBase {

    private static self $instance;
    private Provider $provider;
    private Config $nameCache;

    protected function onLoad(): void {
        self::$instance = $this;
    }

    protected function onEnable(): void {
        // Ensure the plugin folder exists
        @mkdir($this->getDataFolder());
        
        // Save the default config.yml from the resources folder
        $this->saveDefaultConfig();

        // Prevent PHP 8 strict-type warnings by casting to string
        $storageType = strtolower((string) $this->getConfig()->get("storage-type", "yaml"));
        
        // Route to the correct database engine based on the config
        if ($storageType === "mysql" || $storageType === "sqlite") {
            $this->provider = new MySQLProvider($this);
        } else {
            $this->provider = new YamlProvider($this->getDataFolder());
        }
        
        $this->provider->open();

        // Initialize the Name Cache for leaderboards
        $this->nameCache = new Config($this->getDataFolder() . "names.yml", Config::YAML);

        // Register the Event Listener
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener($this), $this);

        // Register all Commands
        $this->getServer()->getCommandMap()->register("daeconomy", new \DaEconomy\command\MoneyCommand($this));
        $this->getServer()->getCommandMap()->register("daeconomy", new \DaEconomy\command\PayCommand($this));
        $this->getServer()->getCommandMap()->register("daeconomy", new \DaEconomy\command\SetMoneyCommand($this));
        $this->getServer()->getCommandMap()->register("daeconomy", new \DaEconomy\command\TopMoneyCommand($this));
        $this->getServer()->getCommandMap()->register("daeconomy", new \DaEconomy\command\BankCommand($this));

        // Fix PMMP Anonymous Task Warning by using a native ClosureTask
        $this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function(): void {
            $this->provider->save();
            $this->nameCache->save();
        }), 20 * 60 * 5);
        
        $this->getLogger()->info("DaEconomy loaded using the " . $this->provider->getName() . " engine!");
    }

    protected function onDisable(): void {
        if (isset($this->provider)) {
            $this->provider->close();
            $this->nameCache->save();
        }
    }

    public static function getInstance(): self {
        return self::$instance;
    }

    public function getProvider(): Provider {
        return $this->provider;
    }

    public function getNameCache(): Config {
        return $this->nameCache;
    }
}
