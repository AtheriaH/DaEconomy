<?php

declare(strict_types=1);

namespace DaEconomy\command;

use DaEconomy\DaEconomy;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class AddMoneyCommand extends Command {

    public function __construct(private DaEconomy $plugin) {
        parent::__construct("addmoney", "Add money to a player balance", "/addmoney <player> <amount>", []);
        $this->setPermission("daeconomy.command.admin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (count($args) < 2) {
            $sender->sendMessage(TextFormat::RED . "Usage: " . $this->getUsage());
            return;
        }

        $amount = (int) $args[1];
        
        if ($amount <= 0) {
            $sender->sendMessage(TextFormat::RED . "Please enter a valid amount greater than zero.");
            return;
        }

        $target = $this->plugin->getServer()->getPlayerByPrefix($args[0]);
        $realName = $target?->getName() ?? $args[0];

        $this->plugin->addBalance($realName, $amount);
        $sender->sendMessage(TextFormat::GREEN . "Successfully added " . $this->plugin->formatMoney($amount) . " to " . $realName);
        
        $target?->sendMessage(TextFormat::GREEN . "You received " . $this->plugin->formatMoney($amount) . ".");
    }
}
