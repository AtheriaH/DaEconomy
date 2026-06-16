<?php

declare(strict_types=1);

namespace DaEconomy\command;

use DaEconomy\DaEconomy;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;

class SetMoneyCommand extends Command {

    public function __construct(private DaEconomy $plugin) {
        parent::__construct("setmoney", "Set a player's exact balance", "/setmoney <player> <amount>");
        // We assign a strict permission node here so regular players can't use it
        $this->setPermission("daeconomy.command.setmoney");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        // Bouncer 1: Does this person have Server Operator (OP) permissions?
        if (!$this->testPermission($sender)) {
            return false;
        }

        // Bouncer 2: Did they provide a name and an amount?
        if (count($args) < 2) {
            $sender->sendMessage(TF::RED . "Usage: /setmoney <player> <amount>");
            return false;
        }

        $targetName = $args[0];
        $amount = (int) $args[1];

        // Bouncer 3: Admin check - we allow 0, but no negative balances
        if ($amount < 0) {
            $sender->sendMessage(TF::RED . "You cannot set a balance lower than zero.");
            return false;
        }

        // We use getPlayerByPrefix so you only have to type part of their name (e.g., /setmoney Ath 500)
        $target = $this->plugin->getServer()->getPlayerByPrefix($targetName);
        if ($target === null) {
            $sender->sendMessage(TF::RED . "Could not find player '$targetName'. Are they online?");
            return false;
        }

        $targetXuid = $target->getXuid();
        $provider = $this->plugin->getProvider();

        // Ensure the account exists before overriding
        if (!$provider->accountExists($targetXuid)) {
            $provider->createAccount($targetXuid);
        }

        // The absolute override action
        $provider->setMoney($targetXuid, $amount);

        // Notify the Admin and the Target
        $sender->sendMessage(TF::GREEN . "You successfully set " . $target->getName() . "'s balance to $" . number_format($amount) . ".");
        $target->sendMessage(TF::YELLOW . "Your bank balance was forcefully updated by an Admin to $" . number_format($amount) . ".");

        return true;
    }
}
