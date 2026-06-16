<?php

declare(strict_types=1);

namespace DaEconomy;

use DaEconomy\provider\Provider;
use DaEconomy\provider\YamlProvider;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\Task;

class DaEconomy extends PluginBase {

    private static self $instance;
    private Provider $provider;

    protected function onLoad(): void {
        self::$instance = $this;
    }

       protected function onEnable(): void {
        // Create the plugin folder if it doesn't exist
        @mkdir($this->getDataFolder());

        // Initialize our custom YAML engine
        $this->provider = new YamlProvider($this->getDataFolder());
        $this->provider->open();

        // Register Commands (Exactly one of each!)
        $this->getServer()->getCommandMap()->register("daeconomy", new \DaEconomy\command\MoneyCommand($this));
        $this->getServer()->getCommandMap()->register("daeconomy", new \DaEconomy\command\PayCommand($this));
        $this->getServer()->getCommandMap()->register("daeconomy", new \DaEconomy\command\SetMoneyCommand($this));

        // The true PM5 way to auto-save every 5 minutes without lagging the server
        $this->getScheduler()->scheduleRepeatingTask(new class($this->provider) extends Task {
            public function __construct(private Provider $provider) {}

            public function onRun(): void {
                $this->provider->save();
            }
        }, 20 * 60 * 5);
        
        $this->getLogger()->info("DaEconomy has been enabled with strict XUID saving!");
    }

    protected function onDisable(): void {
        if (isset($this->provider)) {
            $this->provider->close();
            $this->getLogger()->info("DaEconomy database saved safely.");
        }
    }

    // This allows other plugins (like Shops) to easily grab our database engine
    public static function getInstance(): self {
        return self::$instance;
    }

    public function getProvider(): Provider {
        return $this->provider;
    }
}
