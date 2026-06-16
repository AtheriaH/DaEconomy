<?php

declare(strict_types=1);

namespace DaEconomy;

use DaEconomy\provider\Provider;
use DaEconomy\provider\YamlProvider;
use DaEconomy\provider\MySQLProvider;
use DaEconomy\listener\PlayerListener;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;
use pocketmine\utils\Config;

class DaEconomy extends PluginBase {

    private static self $instance;
    private Provider $provider;
    private Config $nameCache;

    protected function onLoad(): void {
        self::$instance = $this;
    }

    protected function onEnable(): void {
        @mkdir($this->getDataFolder());
        
        // MISSING LINK 1: Save the default config.yml from the resources folder
        $this->saveDefaultConfig();

        // MISSING LINK 2: The Database Router
        $storageType = strtolower($this->getConfig()->get("storage-type", "yaml"));
        
        if ($storageType === "mysql" || $storageType === "sqlite") {
            $this->provider = new MySQLProvider($this);
        } else {
            $this->provider = new YamlProvider($this->getDataFolder());
        }
        
        $this->provider->open();

        // Create the Name Cache file
        $this->nameCache = new Config($this->getDataFolder() . "names.yml", Config::YAML);

        // Register the Event Listener so it tracks players joining
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener($this), $this);

        // Register Commands
        $this->getServer()->getCommandMap()->register("daeconomy", new \DaEconomy\command\MoneyCommand($this));
        $this->getServer()->getCommandMap()->register("daeconomy", new \DaEconomy\command\PayCommand($this));
        $this->getServer()->getCommandMap()->register("daeconomy", new \DaEconomy\command\SetMoneyCommand($this));
        $this->getServer()->getCommandMap()->register("daeconomy", new \DaEconomy\command\TopMoneyCommand($this));
        
        // MISSING LINK 3: The Bank Command Registration!
        $this->getServer()->getCommandMap()->register("daeconomy", new \DaEconomy\command\BankCommand($this));

        // Auto-save task
        $this->getScheduler()->scheduleRepeatingTask(new class($this->provider, $this->nameCache) extends Task {
            public function __construct(private Provider $provider, private Config $nameCache) {}
            public function onRun(): void {
                $this->provider->save();
                $this->nameCache->save();
            }
        }, 20 * 60 * 5);
        
        // We dynamically announce which engine is running!
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
